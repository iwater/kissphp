<?php
/**
 * 定义过滤器基础类
 *
 * PHP versions 5
 *
 * @category KISS
 * @package  Filter
 * @author   iwater <iwater@gmail.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php BSD
 * @link     http://kissphp.cn
 *
 */

/**
 * 定义过滤器基础类
 *
 * @category KISS
 * @package  Filter
 * @author   iwater <iwater@gmail.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php BSD
 * @link     http://kissphp.cn
 */
class KISS_Filter
{
    private $_FilterConfig;

    /**
     * 构造函数
     *
     * @param array $pFilterConfig 过滤器配置
     */
    public function __construct($pFilterConfig = null)
    {
        $this->_FilterConfig = $pFilterConfig;
    }

    /**
     * 返回当前的过滤器配置
     *
     * @return array
     */
    public function getFilterConfig()
    {
        return $this->_FilterConfig;
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