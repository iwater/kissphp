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
 * 定义过滤器基础类
 *
 * @category  Core
 * @package   KISS
 * @author    iwater <iwater@gmail.com>
 * @copyright 2003-2009 iwater
 * @license   http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version   Release: 3.5.0
 * @link      http://www.kissphp.cn
 */
class KISS_Filter
{
    private $_filterConfig;

    /**
     * 构造函数
     *
     * @param array $pFilterConfig 过滤器配置
     */
    public function __construct($pFilterConfig = null)
    {
        $this->_filterConfig = $pFilterConfig;
    }

    /**
     * 返回当前的过滤器配置
     *
     * @return array
     */
    public function getFilterConfig()
    {
        return $this->_filterConfig;
    }

    /**
     * 调用过滤器链上的下一个过滤器
     *
     * @param object      $context 上下文对象
     * @param KISS_Filter $chain   过滤器链
     *
     * @return void
     */
    public final function doFilter($context, $chain = null)
    {
        $this->doPreProcessing($context);
        if (!is_null($chain)) {
            $chain->run();
        }
        $this->doPostProcessing($context);
    }

    /**
     * 预执行方法，在主类执行前执行
     *
     * @return void
     */
    public function doPreProcessing()
    {
    }

    /**
     * 清理方法，在主类结束后执行
     *
     * @return void
     */
    public function doPostProcessing()
    {
    }
}
?>