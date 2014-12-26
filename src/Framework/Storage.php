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
 * KISS_Framework_Storage
 *
 * @category  KISS
 * @package   Core
 * @author    iwater <iwater@gmail.com>
 * @copyright 2003-2009 iwater
 * @license   http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version   Release: 3.5.0
 * @link      http://www.kissphp.cn
 */

class KISS_Framework_Storage
{
    private $_mStorage = array();

    /**
     * 根据键，取得值
     *
     * @param string $pKey 键
     *
     * @return mix
     */
    public function __get($pKey)
    {
        if (array_key_exists($pKey, $this->_mStorage)) {
            return $this->_mStorage[$pKey];
        }
        return null;
    }

    /**
     * 存储键值
     *
     * @param string $pKey   键
     * @param mix    $pValue 值
     *
     * @return void
     */
    public function __set($pKey, $pValue)
    {
        $this->_mStorage[$pKey] = $pValue;
    }
}
?>