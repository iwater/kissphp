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
 * KISS_Framework_Context
 *
 * @category  KISS
 * @package   Core
 * @author    iwater <iwater@gmail.com>
 * @copyright 2003-2009 iwater
 * @license   http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version   Release: 3.5.0
 * @link      http://www.kissphp.cn
 */

class KISS_Framework_Context
{
    static private $_theInstance = null;
    public $mStorage;

    /**
     * 构造函数
     *
     */
    private function __construct()
    {
        $this->mStorage = new KISS_Framework_Storage();
    }

    /**
     * 单态模式，取得全局单一实例
     *
     * @return unknown
     */
    static public function getInstance()
    {
        if (is_null(self::$_theInstance)) {
            self::$_theInstance = new KISS_Framework_Context();
        }
        return self::$_theInstance;
    }

    /**
     * toString
     *
     * @return string
     */
    public function toString()
    {
        return serialize($this->mStorage);
    }

    /**
     * reBuild
     *
     * @param string $pString 序列化文本
     *
     * @return void
     */
    public function reBuild($pString)
    {
        $this->mStorage = unserialize($pString);
    }
}
?>