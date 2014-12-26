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
 * KISS_ORM_BaseTableObject 的代理类
 *
 * @category  KISS
 * @package   Core
 * @author    iwater <iwater@gmail.com>
 * @copyright 2003-2009 iwater
 * @license   http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version   Release: 3.5.0
 * @link      http://www.kissphp.cn
 */

class KISS_ORM_BaseTableObjectProxy extends KISS_Proxy implements IteratorAggregate
{
    public $mMapHash;
    /**
     * 构造函数
     *
     * @param KISS_ORM_BaseTableObject $pClassInstance KISS_ORM_BaseTableObject 对象实例
     * @param object                   $pUser          用户对象实例
     */
    public function __construct(KISS_ORM_BaseTableObject $pClassInstance, $pUser)
    {
        parent:: __construct($pClassInstance, $pUser);
        $this->mMapHash = $this->mObject->mMapHash;
    }

    /**
     * implementation for IteratorAggregate interface
     *
     * @return ArrayIterator
     */
    function getIterator()
    {
        return $this->mObject->getIterator();
    }
}
?>