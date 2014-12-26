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
 * Pagination SQL分页类
 *
 * @category  Core
 * @package   KISS
 * @author    iwater <iwater@gmail.com>
 * @copyright 2003-2009 iwater
 * @license   http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version   Release: 3.5.0
 * @link      http://www.kissphp.cn
 */
class KISS_Util_Pagination
{
    /**
     * 第几页
     *
     * @var int
     */
    public $mPage = 1;
    /**
     * 每页记录条数
     *
     * @var int
     */
    public $mPageSize = 1;
    /**
     * 总页数
     *
     * @var int
     */
    public $mPageCount = 1;
    /**
     * 下一页
     *
     * @var int
     */
    public $mNextPage = 1;
    /**
     * 上一页
     *
     * @var int
     */
    public $mPreviousPage = 1;
    /**
     * 第一页
     *
     * @var int
     */
    public $mFirstPage = 1;
    /**
     * 最后一页
     *
     * @var int
     */
    public $mLastPage = 1;
    /**
     * 总纪录数
     *
     * @var int
     */
    public $mRecordCount = 0;
    /**
     * 纪录起始点
     *
     * @var int
     */
    public $mStartRecord = 0;
    /**
     * 纪录结束点
     *
     * @var int
     */
    public $mEndRecord = 0;
    /**
     * 构造函数
     *
     * @param int $pPage        第几页
     * @param int $pPageSize    每页多少条记录
     * @param int $pRecordCount 记录总数
     */
    function __construct ($pPage = 0, $pPageSize = 10, $pRecordCount = 0)
    {
        $pPage = filter_var($pPage, FILTER_VALIDATE_INT, array(
            'options' => array(
                'min_range' => 0)));
        if ($pPage === false) {
            $pPage = 1;
        }
        $this->mPage     = $pPage;
        $this->mPageSize = $pPageSize;
        $pRecordCount    = filter_var($pRecordCount, FILTER_VALIDATE_INT, array(
            'options' => array(
                'min_range' => 1)));
        if ($pRecordCount !== false) {
            $this->makePage($pRecordCount);
        }
    }
    /**
     * 根据总数分页
     *
     * @param int $pRecordCount 总数
     *
     * @return void
     */
    function makePage ($pRecordCount)
    {
        $this->mRecordCount  = $pRecordCount;
        $this->mPageCount    = (int) ceil($this->mRecordCount / $this->mPageSize);
        $this->mLastPage     = $this->mPageCount;
        $this->mStartRecord  = ($this->mPage - 1) * $this->mPageSize + 1;
        $this->mEndRecord    = min($this->mRecordCount, $this->mPage * $this->mPageSize);
        $this->mNextPage     = min($this->mPageCount, $this->mPage + 1);
        $this->mPreviousPage = max(1, $this->mPage - 1);
    }
    /**
     * 生成HTML属性标签
     *
     * @return string
     */
    function getHtmlAttribute ()
    {
        $page_htc  = "";
        $mPageHash = get_object_vars($this);
        foreach ($mPageHash as $key => $value) {
            $page_htc .= "{$key}={$value} ";
        }
        return $page_htc;
    }
    /**
     * 修改SQL语句
     *
     * @param string $pSql SQL
     *
     * @return string
     */
    function makeSql ($pSql)
    {
        $pSql .= " limit " . ($this->mStartRecord - 1) . "," . $this->mPageSize;
        return $pSql;
    }
}
?>