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
 * @version   SVN: $Id: User.php 109 2008-12-04 06:13:55Z matao $
 * @link      http://www.kissphp.cn
 */

/**
 * 提供JSON服务的控制器
 *
 * @category  KISS
 * @package   Core
 * @author    iwater <iwater@gmail.com>
 * @copyright 2003-2009 iwater
 * @license   http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version   Release: 3.5.0
 * @link      http://www.kissphp.cn
 */

class KISS_Controller_JSON extends KISS_Object
{
    private $_instance;
    /**
     * 构造函数
     *
     * @param string $class_name 类名
     */
    public function __construct($class_name)
    {
        parent::__construct();
        try {
            $context             = KISS_Framework_Context::getInstance();
            $context->mClassName = $class_name;
            $filter              = KISS_Class::getClassConstant($context->mClassName, 'FILTERS');
            $filters             = ($filter=='')?array():explode(',', $filter);
        } catch (Exception $error) {
            $page = new KISS_Page();
            $page->showMessage($error->getMessage());
        }
        while (count($filters) > 0) {
            $filter = array_shift($filters);
            $filter = new $filter();
            $filter->doPreProcessing($context, $this);
        }
    }

    /**
     * 命令行入口函数
     *
     * @return mix
     */
    public function run()
    {
        $context    = KISS_Framework_Context::getInstance();
        $class_name = $context->mClassName;
        if (class_exists($class_name)) {
            if (!isset($_REQUEST['method'])) {
                $this->_genJS();
                die();
            } else {
                try {
                    $old_error_handler = set_error_handler(array('KISS_Controller_JSON',"myErrorHandler"));
                    $this->_instance   = new $class_name();
                } catch (Exception $error) {
                    $this->showMessage($error->getMessage());
                }
            }

        } else {
            $this->showMessage("文件不存在！\\r".$_SERVER['SCRIPT_URI']);
        }
        if (isset($_REQUEST['method'])) {
            $return = array();
            try {
                $old_error_handler = set_error_handler(array('KISS_Controller_JSON', "myErrorHandler"));
                if (get_magic_quotes_gpc()===1) {
                    $_REQUEST['parameters'] = stripslashes($_REQUEST['parameters']);
                }
                $return = array('data'=>call_user_func_array(array(&$this->_instance, $_REQUEST['method']), json_decode($_REQUEST['parameters'])));
            } catch (Exception $error) {
                $return = array('message'=>$error->getMessage());
            }
            echo json_encode($return);
        }
    }

    /**
     * 错误处理函数
     *
     * @param int    $errno      错误代码
     * @param string $errstr     错误信息
     * @param string $errfile    错误文件
     * @param int    $errline    错误行数
     * @param object $errcontext 错误上下文
     *
     * @return bool
     */
    public static function myErrorHandler($errno, $errstr, $errfile, $errline, $errcontext)
    {
        return true;
        $trace_array = debug_backtrace();
        array_shift($trace_array);
        $file = '';
        for ($i=0; $i<count($trace_array); $i++) {
            $file .= (isset($trace_array[$i]['file'])?$trace_array[$i]['file']:serialize($trace_array[$i])).'('.(isset($trace_array[$i]['line'])?$trace_array[$i]['line']:0).")\n";
        }
        $return = array('Exception'=>"{$errfile}({$errline}):{$errstr}", 'option_msg'=>$file);
        die(json_encode($return));
        return true;
    }

    /**
     * 显示消息提示
     *
     * @param string $pMessage 消息
     *
     * @return void
     */
    public function showMessage($pMessage)
    {
        echo "alert('{$pMessage}');";
        die();
    }

    /**
     * 生成JS
     *
     * @return void
     */
    private function _genJS()
    {
        $json_array = KISS_Framework_Config::$annotation['class_function'];
        foreach ($json_array as $class => $value) {
            echo "\n{$class} = {\n  URL : '_JSON_{$class}.php'";
            echo ",\n  name : '{$class}'";
            foreach ($value as $function => $item) {
                echo ",\n  {$function} : function(){KISS.rpc.apply(this, new Array('{$function}',arguments));}";
            }
            echo "\n}";
        }
    }
}
?>