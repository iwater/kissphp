<?php
/**
 * KISS_Object 基础类库，所有开发类的基类
 *
 * PHP versions 5
 *
 * @category KISS
 * @package  Core
 * @author   iwater <iwater@gmail.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php BSD
 * @link     http://kissphp.cn
 *
 */

/**
 * KISS_Object 基础类库，所有开发类的基类
 *
 * @category KISS
 * @package  Core
 * @author   iwater <iwater@gmail.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php BSD
 * @link     http://kissphp.cn
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
            KISS_Util_Debug::setDebugInfo(array($this->UniqueObjectID,get_class($this),'Object','Constructed'));
        }
    }

    /**
     * 析构函数
     *
     */
    public function __destruct()
    {
        if (KISS_Framework_Config::getMode()=='debug') {
            KISS_Util_Debug::setDebugInfo(array($this->UniqueObjectID,get_class($this),'Object','Destructed'));
        }
    }
}
?>