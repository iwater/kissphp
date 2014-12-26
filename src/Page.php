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
 * @category  Core
 * @package   KISS
 * @author    iwater <iwater@gmail.com>
 * @copyright 2003-2009 iwater
 * @license   http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version   SVN: <svn_id>
 * @link      http://www.kissphp.cn
 */
/**
 * 页面基类
 *
 * @category  Core
 * @package   KISS
 * @author    iwater <iwater@gmail.com>
 * @copyright 2003-2009 iwater
 * @license   http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version   Release: 3.5.0
 * @link      http://www.kissphp.cn
 */
class KISS_Page extends KISS_Object implements KISS_Interface_Runnable
{
    public $mSmarty;
    /**
     * 当前用户
     *
     * @var User
     */
    //public $mCurrentUser;
    private $_mParametersSetting = array();
    /**
     * 构造函数
     *
     */
    function __construct ()
    {
        $this->mGET          = $_GET;
        $this->mPOST         = $_POST;
        $type_hash['string'] = FILTER_SANITIZE_STRING;
        $type_hash['int']    = FILTER_VALIDATE_INT;
        $type_hash['email']  = FILTER_VALIDATE_EMAIL;
        parent::__construct();
        $this->mContext     = KISS_Framework_Context::getInstance();
        $this->mCurrentUser = &$this->mContext->mCurrentUser;
        $this->mCurrentTime = time();
        $class              = get_class($this);
        if (isset(KISS_Framework_Config::$annotation['class_var'][$class]) && count(KISS_Framework_Config::$annotation['class_var'][$class]) > 0) {
            foreach (KISS_Framework_Config::$annotation['class_var'][$class] as $key => $value) {
                if (isset($value['source'])) {
                    $source_code = "if(isset({$value['source']}))return {$value['source']};";
                    $source      = eval($source_code);
                    switch ($value['type']) {
                    case 'string':
                        $var = filter_var($source, $type_hash[$value['type']]);
                        if (isset($value['min']) || isset($value['max'])) {
                            $strlen = KISS_Util_String::strlen($source);
                        }
                        if (! is_null($var) && $var != '' && (! isset($value['min']) || $strlen >= $value['min']) && (! isset($value['max']) || $strlen <= $value['max'])) {
                            $this->$key = $var;
                        }
                        break;
                    case 'int':
                        $var = filter_var($source, $type_hash[$value['type']]);
                        if ($var !== false && (! isset($value['min']) || $var >= $value['min']) && (! isset($value['max']) || $var <= $value['max'])) {
                            if (isset($value['class'])) {
                                $this->$key = eval(sprintf('return new %s(%d);', $value['class'], $var));
                            } else {
                                $this->$key = $var;
                            }
                        } elseif (isset($value['require'])) {
                            throw new Exception($value['require']);
                        }
                        break;
                    case 'email':
                        $var = filter_var($source, $type_hash[$value['type']]);
                        if ($var !== false) {
                            $this->$key = $var;
                        }
                        break;
                    case 'timestamp':
                        $var = @strtotime($source);
                        if ($var !== false) {
                            $this->$key = $var;
                        }
                        break;
                    case 'mix':
                        $this->$key = $source;
                        break;
                    default:
                        break;
                    }
                }
            }
        }
    }
    /**
     * Smarty 初始化
     *
     * @return void
     */
    private function _initSmarty ()
    {
        if (is_null($this->mSmarty)) {
            $template_path               = KISS_Framework_Config::getSystemPath('template');
            $template_c_path             = KISS_Framework_Config::getSystemPath('temp') . '/template_c';
            $this->mSmarty               = new Smarty();
            $this->mSmarty->template_dir = $template_path;
            $this->mSmarty->compile_dir  = $template_c_path;
            $this->mSmarty->config_dir   = $template_c_path;
            $this->mSmarty->cache_dir    = $template_c_path;
        }
    }
    /**
     * 显示页面消息
     *
     * @param string $pMessage  消息正文
     * @param array  $pButtons  按钮
     * @param string $pTemplate 模板
     *
     * @return void
     */
    function showMessage ($pMessage, $pButtons = array(), $pTemplate = 'tpl.prompt.htm')
    {
debug_print_backtrace();
var_dump($pMessage);
exit();
        if (is_string($pButtons)) {
            $pButtons = array(
                array(
                    'name' => '确定',
                    'url' => $pButtons));
        }
        if (isset($this->mCurrentUser) && ! empty($this->mCurrentUser->mUsername)) {
            $this->assign('user', $this->mCurrentUser);
        }
        $this->assign('message', $pMessage);
	file_put_contents('/opt/wapcms/WEB-INF/log/error0.log', "{$pMessage}\t".date('[Y-m-d H:i:s]')."\n", FILE_APPEND);
        if (count($pButtons) > 0) {
            $this->assign('buttons', $pButtons);
        }
        $this->display($pTemplate);
        exit();
    }
    /**
     * 显示确认消息
     *
     * @param string $pMessage 消息正文
     *
     * @return void
     */
    function confirm ($pMessage)
    {
        $this->showMessage($pMessage, array(
            array(
                'name' => '确定',
                'url' => '?confirm=yes'),
            array(
                'name' => '取消',
                'url' => 'javascript:history.go(-1);')));
    }
    /**
     * 取得显然完的页面
     *
     * @param string $pTemplate 模板
     *
     * @return string
     */
    function fetch ($pTemplate)
    {
        $this->_initSmarty();
        return $this->mSmarty->fetch($pTemplate);
    }
    /**
     * 清除所有已赋值过的模板变量
     *
     * @return bool
     */
    function clearAllAssign ()
    {
        if (! is_null($this->mSmarty)) {
            return $this->mSmarty->clear_all_assign();
        }
    }
    /**
     * 显示渲染完的页面
     *
     * @param string $pTemplate 模板
     *
     * @return void
     */
    function display ($pTemplate)
    {
        $this->_initSmarty();
        $this->mSmarty->display($pTemplate);
    }
    /**
     * 给模板变量赋值
     *
     * @param string $pKey   变量名
     * @param string $pValue 变量值
     *
     * @return void
     */
    function assign ($pKey, $pValue)
    {
        $this->_initSmarty();
        $this->mSmarty->assign($pKey, $pValue);
    }
    /**
     * 通过变量引用给模板变量赋值，减少内存开销
     *
     * @param string $pKey    变量名
     * @param string &$pValue 变量值
     *
     * @return void
     */
    function assignByRef ($pKey, &$pValue)
    {
        $this->_initSmarty();
        $this->mSmarty->assign_by_ref($pKey, $pValue);
    }
    /**
     * 页面入口函数，其子类需要覆盖此函数
     *
     * @return void
     */
    function run ()
    {
    }
}
?>
