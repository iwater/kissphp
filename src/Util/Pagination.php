<?php
/**
* @author matao <matao@bj.tom.com>
* @version v 1.5 2004/05/08
* @package Core_Class
*/

/**
* Pagination SQL分页类
*/
class KISS_Util_Pagination {
    public $mPage = 1;    //第几页
    public $mPageSize = 1;    //每页记录条数
    public $mPageCount = 1;    //总页数
    public $mNextPage = 1;    //下一页
    public $mPreviousPage = 1;    //上一页
    public $mFirstPage = 1;    //第一页
    public $mLastPage = 1;    //最后一页
    public $mRecordCount = 0;    //总纪录数
    public $mStartRecord = 0;    //纪录起始点
    public $mEndRecord = 0;    //纪录结束点

    function __construct($pPage = 0, $pPageSize = 10, $pRecordCount = 0) {
      $pPage = filter_var($pPage, FILTER_VALIDATE_INT, array('options'  => array('min_range' => 0)));
      if ($pPage === false) {
          $pPage = 1;
      }
        $this->mPage = $pPage;
        $this->mPageSize = $pPageSize;
        
        $pRecordCount = filter_var($pRecordCount, FILTER_VALIDATE_INT, array('options'  => array('min_range' => 1)));

        if($pRecordCount !== false) {
            $this->makePage($pRecordCount);
        }
    }

    function makePage ($pRecordCount) {
        $this->mRecordCount = $pRecordCount;
        $this->mLastPage = $this->mPageCount = (int)ceil($this->mRecordCount / $this->mPageSize);
        $this->mStartRecord = ($this->mPage - 1) * $this->mPageSize + 1;
        $this->mEndRecord = min($this->mRecordCount,$this->mPage * $this->mPageSize);
        $this->mNextPage = min($this->mPageCount,$this->mPage+1);
        $this->mPreviousPage = max(1,$this->mPage-1);
    }

    function getHtmlAttribute() {
        $page_htc = "";
        $mPageHash = get_object_vars($this);
        foreach($mPageHash as $key=>$value) {
            $page_htc .= "{$key}={$value} ";
        }
        return $page_htc;
    }

    function makeSql($pSql) {
        $pSql .= " limit ".($this->mStartRecord-1).",".$this->mPageSize;
    }
}
?>