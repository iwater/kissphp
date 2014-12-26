<?php
/**
 * @author $Author: matao $
 * @version $Id: BaseTableObject.php 106 2008-04-03 10:34:53Z matao $
 * @package KISS
 * @subpackage ORM
 */
/**
 * Table <==> Class 映射基础类库，自动提供基本的插入，更新，删除，搜索功能，根据表结构自动生成相应 SQL 代码，新增字段可保证基本无故障运行
 * @package KISS
 * @subpackage ORM
 */
class KISS_ORM_BaseTableObject extends KISS_Object implements ArrayAccess,IteratorAggregate  {
    /**
   * 当前对象对应表信息
   *
   * @var array
   */
    public $mTableHash;
    /**
   * 当前对象对应表字段嘻嘻
   *
   * @var array
   */
    public $mTableFieldHash;
    /**
     * 条件提供类
     *
     * @var KISS_ORM_BaseTableObject
     */
    public $mObjectSource;
    /**
     * 应用目标类
     *
     * @var KISS_ORM_BaseTableObject
     */
    public $mObjectDestination;
    /**
   * SqlCommand 对象实例
   *
   * @var KISS_KDO_SqlCommand
   */
    public $SqlCommand;
    /**
     * 附加条件
     *
     * @var string
     */
    public $mAdditionalCondition;

    /**
   * 最后一条执行的SQL
   *
   * @var string
   */
    public $mLastSQLQuery;
    public $mPagination;

    /**
   * 表状态
   *
   * @var array
   */
    public $mTableStatus = array();

    /**
   * 观察者对象数组
   *
   * @var array
   */
    private $observers = array();
    private $state;

    /**
   * 构造函数
     *
     * @param string $pTableName 数据库表名
     * @param int $pDBConfig 数据库连接配置文件中配置ID
     * @access public
     */
    public function __construct($pTableName, $pDBConfig = 0) {
        parent::__construct();
        $this->SqlCommand = &KISS_KDO_SqlCommand::getInstance($pDBConfig);
        $this->mTableHash = array('name' => $pTableName);
        $this->prepareMapHash();
    }

    /**
   * 初始化字段映射数组
     *
     * @access private
     */
    private function prepareMapHash() {
        $file_cache = new KISS_Util_FileCache('0'.$this->mTableHash['name']);
        if ($file_cache->check_cache_status()) {
            $this->mTableFieldHash = unserialize($file_cache->get_cache_content());
        } else {
            $this->mTableFieldHash = $this->SqlCommand->getTableFieldHash($this->mTableHash['name']);
            if (count($this->mTableFieldHash) > 0) {
                if(KISS_Framework_Config::getMode()=="online") {
                    //如果系统运行在online模式下,则记录缓存,默认是online模式
                    $file_cache->put_cache_content(serialize($this->mTableFieldHash));
                }
            } else {
                throw new Exception('数据库访问错误！');
            }
        }

        $keys = array_keys($this->mTableFieldHash);
        for ($i = 0; $i < count($this->mTableFieldHash); $i++) {
            $member_name = "m" . KISS_Util_Util::magicName($keys[$i]);
            $this->mMapHash[$keys[$i]] = &$this->$member_name;
        }

        $file_cache = new KISS_Util_FileCache('1'.$this->mTableHash['name']);
        if ($file_cache->check_cache_status()) {
            $this->mTableHash['key'] = unserialize($file_cache->get_cache_content());
        }
        else {
            $this->mTableHash['key'] = $this->SqlCommand->getTablePrimeKey($this->mTableHash['name']);
            if(KISS_Framework_Config::getMode()=="online") {
                //如果系统运行在online模式下,则记录缓存,默认是online模式
                $file_cache->put_cache_content(serialize($this->mTableHash['key']));
            }
        }
    }

    /**
   * 构造SQL语句
     *
     * @access private
     */
    private function prepareSQL($pType = 0, $pObjectSource = null) {
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
            case 2://主键方式
            foreach ($ObjectSource->mTableHash['key'] as $key) {
                $return[] = "{$key} = {$array[$key]}";
            }
            break;
            case 3://for insert
            foreach($array as $key => $value) {
                $return[0][] = $key;
                $return[1][] = $value;
            }
            break;
        }
        return $return;
    }

    /**
   * 判断字段是否需加引号
     *
     * @access private
     */
    private function isDigtialSQLType($pType) {
        return !($pType == "string" || $pType == "blob");
    }


    /**
   * 生成SQL语句
   *
   * @param string $pOrder
   * @param int $pSmartCode
   * @param string $pColumns
   * @param string $pGroupBy
   */
    private function generateSql($pOrder = '', $pSmartCode = 1, $pColumns = '*', $pGroupBy = '') {
        $tGroupBy = '';
        $tOrderBy = '';
        $tCondition = $this->getCondition($pSmartCode, is_null($this->mObjectSource)?$this:$this->mObjectSource);
        if (!empty($pGroupBy)) {
            $tGroupBy = "GROUP BY {$pGroupBy}";
        }
        if (!empty($pOrder)) {
            $tOrderBy = "ORDER BY {$pOrder}";
        }
        $this->mLastSQLQuery = "SELECT {$pColumns} FROM {$this->mTableHash['name']}{$tCondition} {$tGroupBy} {$tOrderBy}";
        $this->mLastCountSQLQuery = "SELECT COUNT(".(empty($tGroupBy)?"*":"DISTINCT {$pGroupBy}").") FROM {$this->mTableHash['name']}{$tCondition}";
    }

    /**
   * 填充实体类属性值
   *
   * @param string $pKey
   * @param mix $pValue
   */
    public function _setObjectData($pKey, $pValue) {
        if (array_key_exists($pKey,$this->mMapHash)) {
            $this->mMapHash[$pKey] = $pValue;
        }
    }

    /**
   * 批量填充实体类属性值
   *
   * @param array $pKey
   * @param mix $pValues
   */
    public function _set_object_data($pValues) {
        foreach ($pValues as $key => $value){
            $this->_setObjectData($key, $value);
        }
    }

    /**
   * 获取实体类属性值
   *
   * @param string $pKey
   * @return mix
   */
    public function _getObjectData($pKey) {
        if (array_key_exists($pKey,$this->mMapHash)) {
            return $this->mMapHash[$pKey];
        }
        return null;
    }

    /**
   * 根据当前对象属性，插入一条新记录到对应表中
     *
     * @access public
     */
    public function _insert() {
        if(get_class($this->SqlCommand)=='SqlCommand') {
            $equals = implode(",", $this->prepareSQL(0, $this->mObjectDestination));
            $sql = "INSERT IGNORE INTO {$this->mTableHash['name']} SET {$equals}";
        }
        else {
            $columns = $this->prepareSQL(3, $this->mObjectDestination);
            $equals[0] = implode(",", $columns[0]);
            $equals[1] = implode(",", $columns[1]);
            $sql = "INSERT INTO {$this->mTableHash['name']} ({$equals[0]}) VALUES ({$equals[1]})";
        }
        $return = $this->SqlCommand->ExecuteInsertQuery($sql);
        $this->setState('object inserted');
        $this->notifyObservers();
        return $return;
    }

    /**
   * 更新当前对象到数据库中
   *
   * @param int $pSmartCode
   * @return bool
   */
    public function _update($pSmartCode = 0) {
        if (!empty($this->mObjectSource)) {
            $ObjectSource = &$this->mObjectSource;
        }
        else {
            $ObjectSource = &$this;
        }
        if(empty($this->mObjectSource) && empty($this->mObjectDestination)) {
            $condition_array = $this->prepareSQL(2, $this->mObjectSource);
        }
        else {
            $condition_array = $this->prepareSQL($pSmartCode, $this->mObjectSource);
        }
        if (!empty($ObjectSource->mAdditionalCondition)) {
            array_push($condition_array, "({$ObjectSource->mAdditionalCondition})");
        }
        if (count($condition_array) > 0) {
            $condition = " WHERE " . implode(" and ", $condition_array);
        }
        $condition = $this->getCondition($pSmartCode, $ObjectSource);
        $equals = implode(",", $this->prepareSQL(0, $this->mObjectDestination));

        $sql = "UPDATE {$this->mTableHash['name']} SET {$equals}{$condition}";

        if ($this->SqlCommand->ExecuteNonQuery($sql)) {
            $this->setState('object updated');
            $this->notifyObservers();
            return true;
        }
        return false;
    }

    /**
    * 刷新方法，如果存在符合条件的记录，则更新相应字段，否则，插入一条新的记录，必须有主键存在且主键已赋值
    * @access public
    */
    public function _refresh() {
        $keys = array_keys($this->mMapHash);
        foreach ($keys as $key ) {
            if(!in_array($key,$this->mTableHash['key'])) {
                $mMapHash[$key] = $this->mMapHash[$key];
                $this->mMapHash[$key] = null;
            }
        }
        if($this->_select()) {
            $this->mMapHash = array_merge($this->mMapHash,$mMapHash);
            $this->_update();
        }
        else {
            $this->mMapHash = array_merge($this->mMapHash,$mMapHash);
            $this->_insert();
        }
    }

    /**
   * 删除当前对象对应的记录
   *
   * @param int $pSmartCode 是否精确匹配,默认为精确匹配
   * @return bool
   */
    public function _delete($pSmartCode = 0) {
        $sql = "DELETE FROM {$this->mTableHash['name']}".$this->getCondition($pSmartCode, $this);
        $return = $this->SqlCommand->ExecuteNonQuery($sql);
        $this->setState('object deleted');
        $this->notifyObservers();
        return $return;
    }

    /**
   * 从对应表中选取对应的一条记录
   *
   * @param int $pLimit 偏移量，1为第一个
   * @param string $pOrder 排序字段
   * @return bool
   */
    public function _select($pLimit = 1,$pOrder=NULL) {
        $smart_code = ($pLimit > 1)?1:0;
        $result = $this->_list($pLimit, 1, $pOrder, $smart_code);
        if (count($result)>0) {
            return $this->_fill($result[0]);
        }
        return false;
    }

    /**
   * 把输入数组填充到当前对象中
   *
   * @param array $pArray
   * @return bool
   */
    function _fill(&$pArray) {
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
    function _list($pPage = 0, $pPageSize = 10, $pOrder = null, $pSmartCode = 1, $pColumns = '*', $pGroupBy = '') {
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
    function _list_as_iterator($pPage = 0, $pPageSize = 10, $pOrder = null, $pSmartCode = 1, $pColumns = '*', $pGroupBy = '') {
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

    function _getPagination() {
        return $this->mPagination;
    }

    /**
    *
    * @access public
    */
    function _getObjects($pPage = 0, $pPageSize = 10, $pOrder = null, $pSmartCode = 1, $pColumns = '*', $pGroupBy = '') {
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
   * 更新当前表状态
   *
   */
    private function updateTableStatus() {
        $sql = "SHOW TABLE STATUS LIKE '{$this->mTableHash['name']}'";
        $this->mTableStatus = $this->SqlCommand->ExecuteArrayQuery($sql);
    }

    /**
   * 得到符合条件的记录总数
   *
   * @param int $pSmartCode 是否精确匹配，1为模糊(默认),0为精确
   * @param string $pColumns
   * @param string $pGroupBy
   * @return int
   */
    public function _count ($pSmartCode = 1, $pColumns = '*', $pGroupBy = '') {
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
   * 重置当前对象，把当前对象与表字段对应的值全部置为NULL
   *
   */
    public function _reset() {
        $keys = array_keys($this->mMapHash);
        $length = count($keys);
        for($i =0; $i<$length; $i++) {
            $this->mMapHash[$keys[$i]] = null;
        }
    }

    /**
   * 生成SQL语句的条件子句
   *
   * @param int $pSmartCode 是否精确匹配，1为模糊(默认),0为精确
   * @param KISS_ORM_BaseTableObject $pObject
   * @return string
   */
    function getCondition($pSmartCode = 0, $pObject) {
        if (function_exists('array_intersect_key')) {
            $array = array_intersect_key($this->mMapHash,array_flip($this->mTableHash['key']));
        }
        else {
            $keys = array_intersect(array_keys($this->mMapHash),$this->mTableHash['key']);
            foreach ($keys as $key) {
                $array[$key] = $this->mMapHash[$key];
            }
        }
        if (array_reduce($array, create_function('$a,$b','return ($a & !is_null($b));'), true)) {
            $condition_array = $this->prepareSQL(2, $pObject);
        }
        else {
            $condition_array = $this->prepareSQL($pSmartCode, $pObject);
            if (!empty($pObject->mAdditionalCondition)) {
                array_push($condition_array, "({$pObject->mAdditionalCondition})");
            }
        }
        if (count($condition_array) > 0) {
            return " WHERE " . implode(" and ", $condition_array);
        }
        return '';
    }

    function offsetExists($key) {
        return key_exists($key, $this->mMapHash);
    }
    function offsetGet($key) {
        return $this->mMapHash[$key];
    }
    function offsetSet($key, $value) {
        $this->mMapHash[$key] = $value;
    }
    function offsetUnset($key) {
        unset($this->mMapHash[$key]);
    }

    function getIterator() {
        return new ArrayIterator($this->mMapHash);
    }

    function notifyObservers () {
        $observers=count($this->observers);
        for ($i=0;$i<$observers;$i++) {
            $this->observers[$i]->update($this);
        }
    }

    function addObserver (& $observer) {
        $this->observers[]=& $observer;
    }

    function getState () {
        return $this->state;
    }

    function setState ($state) {
        $this->state=$state;
    }

    /**
   * 设置条件对象属性：不改版原始对象，条件属性设置到mObjectDestination
   * @access protected
   * @param string $pProperty 属性名，是属性对应的字段名
   * @param mixed $pValue 属性值
   * @return void
   */
    protected function set_destination_property($pProperty, $pValue) {
        if (!isset($this->mObjectDestination) || !is_object($this->mObjectDestination)) {
            $class_name = get_class($this);
            $this->mObjectDestination = new $class_name;
        }
        $this->mObjectDestination->mMapHash[$pProperty] = $pValue;
    }

    /**
   * 设置条件对象属性：不改版原始对象，条件属性设置到mObjectSource
   * @access protected
   * @param string $pProperty 属性名，是属性对应的字段名
   * @param mixed $pValue 属性值
   * @return void
   */
    protected function set_condition_property($pProperty, $pValue) {
        if (isset($this->mObjectSource) && !is_object($this->mObjectSource)) {
            $class_name = get_class($this);
            $this->mObjectSource = new $class_name;
        }
        $this->mObjectSource->mMapHash[$pProperty] = $pValue;
    }
}
?>