<?php
/**
 * @author $Author: matao $
 * @version $Id: BaseViewObject.php 114 2008-12-05 09:46:23Z matao $
 * @package KISS
 * @subpackage ORM
 */
/**
 * View <==> Class 映射基础类库，自动提供基本，搜索功能，根据表结构自动生成相应 SQL 代码，新增字段可保证基本无故障运行
 * @package KISS
 * @subpackage ORM
 */
class KISS_ORM_BaseViewObject extends KISS_Object implements ArrayAccess,IteratorAggregate
{
    public $mTableHash;
    public $mTableFieldHash;

    public $SqlCommand;
    /**
    * 附加条件
    */
    public $mAdditionalCondition;

    public $mLastSQLQuery;
    public $mPagination;

    public $mTableStatus = array();

    /**
    *
    * @param string $pTableName 数据库表名
    * @param int $pDBConfig 数据库连接配置文件中配置ID
    * @access public
    */
    function __construct($pTableName, $pDBConfig = 0)
    {
        parent::__construct();
        $this->SqlCommand = &KISS_KDO_SqlCommand::getInstance($pDBConfig);
        $this->mTableHash = array('name' => $pTableName);
        $this->prepareMapHash();
    }

    /**
    *
    * @access private
    */
    function prepareMapHash()
    {
        $file_cache = new KISS_Util_FileCache('0'.$this->mTableHash['name']);
        if ($file_cache->checkCacheStatus()) {
            $this->mTableFieldHash = unserialize($file_cache->getCacheContent());
        }
        else {
            $this->mTableFieldHash = $this->SqlCommand->getTableFieldHash($this->mTableHash['name']);
            if (count($this->mTableFieldHash) > 0) {
                if(KISS_Framework_Config::getMode()=="online") {
                    //如果系统运行在online模式下,则记录缓存,默认是online模式
                    $file_cache->putCacheContent(serialize($this->mTableFieldHash));
                }
            }
            else {
                die('数据库访问错误！');
            }
        }

        $keys = array_keys($this->mTableFieldHash);
        for ($i = 0; $i < count($this->mTableFieldHash); $i++) {
            $member_name = "m" . KISS_Util_Util::magicName($keys[$i]);
            $this->mMapHash[$keys[$i]] = &$this->$member_name;
        }

        $file_cache = new KISS_Util_FileCache('1'.$this->mTableHash['name']);
        if ($file_cache->checkCacheStatus()) {
            $this->mTableHash['key'] = unserialize($file_cache->getCacheContent());
        }
        else {
            $this->mTableHash['key'] = $this->SqlCommand->getTablePrimeKey($this->mTableHash['name']);
            if(KISS_Framework_Config::getMode()=="online") {
                //如果系统运行在online模式下,则记录缓存,默认是online模式
                $file_cache->putCacheContent(serialize($this->mTableHash['key']));
            }
        }
    }

    /**
    *
    * @access private
    */
    function prepareSQL($pType = 0, $pObjectSource = null)
    {
        $ObjectSource = (!empty($pObjectSource))?$pObjectSource:$this;
        $return = array();
        $array = array();

        foreach($ObjectSource->mMapHash as $key => $value) {
            if (isset($ObjectSource->mMapHash[$key])) {
                if ('int' != $ObjectSource->mTableFieldHash[$key]['type'] && 'bigint' != $ObjectSource->mTableFieldHash[$key]['type']) {
                    $array[$key] = "'".mysql_escape_string($value)."'";
                }
                else {
                    $array[$key] = intval($value);
                }
            }
        }

        switch ($pType) {
            case 0://精确
            foreach($array as $key => $value) {
                $return[] = "{$key} = {$value}";
            }
            break;

            case 1://模糊
            foreach($array as $key => $value) {
                if ($ObjectSource->isDigtialSQLType($ObjectSource->mTableFieldHash[$key]['type'])) {
                    if(!is_array($value)) {
                        $value = explode(',',$value);
                    }
                    sort($value);
                    if(count($value) == 2) {
                        $return[] = "{$key} >= {$value[0]} and {$key} < {$value[1]}";
                    }
                    elseif(count($value) == 1) {
                        $return[] = "{$key} = {$value[0]}";
                    }
                    else {
                        $return[] = "{$key} = ".implode(" or {$key} =",$value);
                    }
                }
                else {
                    $return[] = "{$key} like '%" . addslashes(trim($value,"'")) . "%'";
                }
            }
            break;
        }
        return $return;
    }

    /**
    *
    * @access private
    */
    function isDigtialSQLType($pType)
    {
        return !($pType == "string" || $pType == "blob");
    }

    /**
    *
    * @access private
    */
    function generateSql($pOrder = '', $pSmartCode = 1, $pColumns = '*', $pGroupBy = '')
    {
        $tGroupBy = '';
        $tOrderBy = '';
        $tCondition = $this->getCondition($pSmartCode, $this);
        if (!empty($pGroupBy)) {
            $tGroupBy = "GROUP BY {$pGroupBy}";
        }
        if (!empty($pOrder)) {
            $tOrderBy = "ORDER BY {$pOrder}";
        }
        $this->mLastSQLQuery = "SELECT {$pColumns} FROM {$this->mTableHash['name']}{$tCondition} {$tGroupBy} {$tOrderBy}";
        $this->mLastCountSQLQuery = "SELECT COUNT(".(empty($tGroupBy)?"*":"DISTINCT {$pGroupBy}").") FROM {$this->mTableHash['name']}{$tCondition}";
    }

    function _setObjectData($pKey, $pValue)
    {
        if (array_key_exists($pKey,$this->mMapHash)) {
            $this->mMapHash[$pKey] = $pValue;
        }
    }

    function _getObjectData($pKey)
    {
        if (array_key_exists($pKey,$this->mMapHash)) {
            return $this->mMapHash[$pKey];
        }
        return null;
    }

    /**
    *
    * @access public
    */
    function _select($pLimit = 1,$pOrder=NULL)
    {
        if($pLimit > 1) {
            $result = $this->_list($pLimit, 1, $pOrder, 1);
        }
        else {
            $result = $this->_list($pLimit, 1, $pOrder, 0);
        }
        if (count($result)>0) {
            return $this->_fill($result[0]);
        }
        return false;
    }

    /**
    *
    * @access public
    */
    function _fill(&$pArray)
    {
        if (is_array($pArray) && count($pArray) > 0) {
            foreach($pArray as $key => $value) {
                $this->mMapHash[$key] = $value;
            }
            return true;
        }
        return false;
    }

    /**
    *
    * @access public
    */
    function _list($pPage = 0, $pPageSize = 10, $pOrder = null, $pSmartCode = 1, $pColumns = '*', $pGroupBy = '')
    {
        if($pPage != 0 && $pPageSize!=1) {
            $count = $this->_count($pSmartCode,$pColumns,$pGroupBy);
            //$this->mPagination = &new Pagination(1,1);
            $this->mPagination = new Pagination ($pPage, $pPageSize, $count);
        }
        if (!($pPage>0 && $pPageSize==1) && empty($pOrder) && count($this->mTableHash['key']) > 0) {
            $pOrder = implode(",", $this->mTableHash['key']);
        }
        $this->generateSql($pOrder, $pSmartCode, $pColumns, $pGroupBy);
        //$this->updateTableStatus();
        if(KISS_Framework_Config::isCached()) {
            return $this->SqlCommand->ExecuteCacheArrayQuery($this->mLastSQLQuery, $pPage, $pPageSize, 'assoc', array($this->mTableHash['name']));
        }
        else {
            return $this->SqlCommand->ExecuteArrayQuery($this->mLastSQLQuery, $pPage, $pPageSize, 'assoc');
        }
    }

    /**
    *
    * @access public
    */
    function _list_as_iterator($pPage = 0, $pPageSize = 10, $pOrder = null, $pSmartCode = 1, $pColumns = '*', $pGroupBy = '')
    {
        if($pPage != 0 && $pPageSize!=1) {
            $count = $this->_count($pSmartCode,$pColumns,$pGroupBy);
            //$this->mPagination = &new Pagination(1,1);
            $this->mPagination = new Pagination ($pPage, $pPageSize, $count);
        }
        if (!($pPage>0 && $pPageSize==1) && empty($pOrder) && count($this->mTableHash['key']) > 0) {
            $pOrder = implode(",", $this->mTableHash['key']);
        }
        $this->generateSql($pOrder, $pSmartCode, $pColumns, $pGroupBy);
        //$this->updateTableStatus();
        if(KISS_Framework_Config::isCached()) {
            return $this->SqlCommand->ExecuteCacheArrayQuery($this->mLastSQLQuery, $pPage, $pPageSize, 'assoc', array($this->mTableHash['name']));
        }
        else {
            return $this->SqlCommand->ExecuteIteratorQuery($this->mLastSQLQuery, $pPage, $pPageSize, 'assoc');
        }
    }

    function _getPagination()
    {
        return $this->mPagination;
    }

    /**
    *
    * @access public
    */
    function _list_as_object($pPage = 0, $pPageSize = 10, $pOrder = null, $pSmartCode = 1, $pColumns = '*', $pGroupBy = '')
    {
        $arrays = $this->_list($pPage, $pPageSize, $pOrder, $pSmartCode, $pColumns, $pGroupBy);
        $name = get_class($this);
        $return = array();
        for ($i=0; $i<count($arrays); $i++) {
            $return[$i] = new $name;
            $return[$i]->_fill($arrays[$i]);
        }
        return $return;
    }

    /**
    *
    * @access private
    */
    function updateTableStatus()
    {
        $sql = "SHOW TABLE STATUS LIKE '{$this->mTableHash['name']}'";
        $this->mTableStatus = $this->SqlCommand->ExecuteArrayQuery($sql);
    }

    /**
    *
    * @access public
    */
    function _count ($pSmartCode = 1, $pColumns = '*', $pGroupBy = '')
    {
        $this->generateSql('', $pSmartCode, $pColumns, $pGroupBy);
        if(KISS_Framework_Config::isCached()) {
            $return = $this->SqlCommand->ExecuteCacheArrayQuery($this->mLastCountSQLQuery, 0, 10, 'num', array($this->mTableHash['name']));
        }
        else {
            $return = $this->SqlCommand->ExecuteArrayQuery($this->mLastCountSQLQuery, 0, 10, 'num');
        }
        return $return[0][0];
    }

    /**
    *
    * @access public
    */
    function _reset()
    {
        $keys = array_keys($this->mMapHash);
        $length = count($keys);
        for($i =0; $i<$length; $i++) {
            $this->mMapHash[$keys[$i]] = null;
        }
    }

    /**
    *
    * @access private
    */
    function magicName ($pString)
    {
        return implode("", array_map('ucfirst', explode('_', $pString)));
    }

    /**
    *
    * @access private
    */
    function getCondition($pSmartCode = 0, $pObject)
    {
        $condition_array = $this->prepareSQL($pSmartCode, $pObject);
        if (!empty($pObject->mAdditionalCondition)) {
            array_push($condition_array, "({$pObject->mAdditionalCondition})");
        }
        if (count($condition_array) > 0) {
            return " WHERE " . implode(" and ", $condition_array);
        }
        return '';
    }

    function offsetExists($key)
    {
        return key_exists($key, $this->mMapHash);
    }

    function offsetGet($key)
    {
        return $this->mMapHash[$key];
    }

    function offsetSet($key, $value)
    {
        $this->mMapHash[$key] = $value;
    }

    function offsetUnset($key)
    {
        unset($this->mMapHash[$key]);
    }

    /**
     * 迭代自身字段影射表
     *
     * @return ArrayIterator
     */
    function getIterator()
    {
        return new ArrayIterator($this->mMapHash);
    }
}
?>