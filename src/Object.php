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
 * 基础类，所有开发类的基类
 *
 * @category  Core
 * @package   KISS
 * @author    iwater <iwater@gmail.com>
 * @copyright 2003-2009 iwater
 * @license   http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version   Release: 3.5.0
 * @link      http://www.kissphp.cn
 */

abstract class KISS_Object
{
    /**
     * 当前实例的标识ID
     *
     * @var string
     */
    protected $UniqueObjectID = 'init';

    /**
     * 构造函数
     *
     */
    public function __construct()
    {
        if (KISS_Framework_Config::getMode()=='debug') {
            if (function_exists('spl_object_hash')) {
                $this->UniqueObjectID = spl_object_hash($this);
            } else {
                $this->UniqueObjectID = uniqid();
            }
            $info_array = array($this->UniqueObjectID,get_class($this),'Object','Constructed');
            KISS_Util_Debug::setDebugInfo($info_array);
        }
    }

    /**
     * 析构函数
     *
     */
    public function __destruct()
    {
        if (KISS_Framework_Config::getMode()=='debug') {
            $info_array = array($this->UniqueObjectID,get_class($this),'Object','Destructed');
            KISS_Util_Debug::setDebugInfo($info_array);
        }
    }
}
?>