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
 * Table <==> Class 映射基础类库，自动提供基本的插入，更新，删除，搜索功能，根据表结构自动生成相应 SQL 代码，新增字段可保证基本无故障运行
 *
 * @category  KISS
 * @package   Core
 * @author    iwater <iwater@gmail.com>
 * @copyright 2003-2009 iwater
 * @license   http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version   Release: 3.5.0
 * @link      http://www.kissphp.cn
 */
class KISS_ORM_BaseTableObject extends KISS_Object implements ArrayAccess, IteratorAggregate
{
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
    private $_observers = array();
    private $_state;
    private $_needUpdate = FALSE;
    /**
     * 构造函数
     *
     * @param string $pTableName 数据库表名
     * @param int    $pDBConfig  数据库连接配置文件中配置ID
     *
     * @return void
     */
    public function __construct ($pTableName, $pDBConfig = 0)
    {
        parent::__construct();
        $this->SqlCommand = &KISS_KDO_SqlCommand::getInstance($pDBConfig);
        $this->mTableHash = array(
            'name' => $pTableName);
        $this->_prepareMapHash();
var_dump($this->mMapHash);
    }
    public function __call ($name, $arguments)
    {
        if (array_key_exists($name, $this->mMapHash)) {
            if (count($arguments) == 0) {
                return $this->mMapHash[$name];
            } else {
                $member_name = "m" . KISS_Util_Util::magicName($name);
                $this->$member_name = $arguments[0];
                return $this;
            }
        }
    }
    /**
     * 初始化字段映射数组
     *
     * @return void
     */
    private function _prepareMapHash ()
    {
        $file_cache = new KISS_Util_FileCache('0' . $this->mTableHash['name']);
        if ($file_cache->checkCacheStatus()) {
            $this->mTableFieldHash = unserialize($file_cache->getCacheContent());
        } else {
            $this->mTableFieldHash = $this->SqlCommand->getTableFieldHash($this->mTableHash['name']);
            if (count($this->mTableFieldHash) > 0) {
                if (KISS_Framework_Config::getMode() == "online") {
                    //如果系统运行在online模式下,则记录缓存,默认是online模式
                    $file_cache->putCacheContent(serialize($this->mTableFieldHash));
                }
            } else {
                throw new Exception('数据库访问错误！');
            }
        }
        $keys = array_keys($this->mTableFieldHash);
        for ($i = 0; $i < count($this->mTableFieldHash); $i ++) {
            $member_name = "m" . KISS_Util_Util::magicName($keys[$i]);
            $this->mMapHash[$keys[$i]] = &$this->$member_name;
        }
        $file_cache = new KISS_Util_FileCache('1' . $this->mTableHash['name']);
        if ($file_cache->checkCacheStatus()) {
            $this->mTableHash['key'] = unserialize($file_cache->getCacheContent());
        } else {
            $this->mTableHash['key'] = $this->SqlCommand->getTablePrimeKey($this->mTableHash['name']);
            if (KISS_Framework_Config::getMode() == "online") {
                //如果系统运行在online模式下,则记录缓存,默认是online模式
                $file_cache->putCacheContent(serialize($this->mTableHash['key']));
            }
        }
    }
    /**
     * 构造SQL语句
     *
     * @param int    $pType         构造类型
     * @param object $pObjectSource 原对象实例
     *
     * @return array
     */
    private function _prepareSQL ($pType = 0, $pObjectSource = null)
    {
        $ObjectSource = (! empty($pObjectSource)) ? $pObjectSource : $this;
        $return = array();
        $array = array();
        foreach ($ObjectSource->mMapHash as $key => $value) {
            if (isset($ObjectSource->mMapHash[$key])) {
                if ('int' != $ObjectSource->mTableFieldHash[$key]['type'] && 'bigint' != $ObjectSource->mTableFieldHash[$key]['type']) {
                    $array[$key] = "'" . mysql_escape_string($value) . "'";
                } else {
                    $array[$key] = $value;
                }
            }
        }
        switch ($pType) {
        case 0: //精确
            foreach ($array as $key => $value) {
                $return[] = "{$key} = {$value}";
            }
            break;
        case 1: //模糊
            foreach ($array as $key => $value) {
                if ($ObjectSource->_isDigtialSQLType($ObjectSource->mTableFieldHash[$key]['type'])) {
                    if (! is_array($value)) {
                        $value = explode(',', $value);
                    }
                    sort($value);
                    if (count($value) == 2) {
                        $return[] = "{$key} >= {$value[0]} and {$key} < {$value[1]}";
                    } elseif (count($value) == 1) {
                        $return[] = "{$key} = {$value[0]}";
                    } else {
                        $return[] = "{$key} = " . implode(" or {$key} =", $value);
                    }
                } else {
                    $return[] = "{$key} like '%" . addslashes(trim($value, "'")) . "%'";
                }
            }
            break;
        case 2: //主键方式
            foreach ($ObjectSource->mTableHash['key'] as $key) {
                $return[] = "{$key} = {$array[$key]}";
            }
            break;
        case 3: //for insert
            foreach ($array as $key => $value) {
                $return[0][] = $key;
                $return[1][] = $value;
            }
            break;
        }
        return $return;
    }
    /**
     * 判断字段是否数字类型
     *
     * @param string $pType 类型
     *
     * @return bool
     */
    private function _isDigtialSQLType ($pType)
    {
        return ! ($pType == "string" || $pType == "blob");
    }
    /**
     * 生成SQL语句
     *
     * @param mix    $pOrder     排序字段
     * @param int    $pSmartCode 是否模糊匹配
     * @param string $pColumns   需要提取的字段
     * @param string $pGroupBy   需要合并的字段
     *
     * @return void
     */
    private function _generateSql ($pOrder = '', $pSmartCode = 1, $pColumns = '*', $pGroupBy = '')
    {
        $tGroupBy = '';
        $tOrderBy = '';
        $tCondition = $this->getCondition($pSmartCode, is_null($this->mObjectSource) ? $this : $this->mObjectSource);
        if (! empty($pGroupBy)) {
            $tGroupBy = "GROUP BY {$pGroupBy}";
        }
        if (! empty($pOrder)) {
            $tOrderBy = "ORDER BY {$pOrder}";
        }
        $this->mLastSQLQuery = "SELECT {$pColumns} FROM {$this->mTableHash['name']}{$tCondition} {$tGroupBy} {$tOrderBy}";
        $this->mLastCountSQLQuery = "SELECT COUNT(" . (empty($tGroupBy) ? "*" : "DISTINCT {$pGroupBy}") . ") FROM {$this->mTableHash['name']}{$tCondition}";
    }
    /**
     * 填充实体类属性值
     *
     * @param string $pKey   数据库字段名
     * @param mix    $pValue 需要填充的值
     *
     * @return void
     */
    public function _setObjectData ($pKey, $pValue)
    {
        if (array_key_exists($pKey, $this->mMapHash)) {
            $this->mMapHash[$pKey] = $pValue;
        }
    }
    /**
     * 批量填充实体类属性值
     *
     * @param array $pValues 需要进行填充的哈希数组
     *
     * @return void
     */
    public function _set_object_data ($pValues)
    {
        foreach ($pValues as $key => $value) {
            $this->_setObjectData($key, $value);
        }
    }
    /**
     * 根据数据库原始字段名，获取实体类属性值
     *
     * @param string $pKey 数据库原始字段名
     *
     * @return mix
     */
    public function _getObjectData ($pKey)
    {
        if (array_key_exists($pKey, $this->mMapHash)) {
            return $this->mMapHash[$pKey];
        }
        return null;
    }
    /**
     * 根据当前对象属性，插入一条新记录到对应表中
     *
     * @return void
     */
    public function _insert ()
    {
        if (get_class($this->SqlCommand) == 'SqlCommand') {
            $equals = implode(",", $this->_prepareSQL(0, $this->mObjectDestination));
            $sql = "INSERT IGNORE INTO {$this->mTableHash['name']} SET {$equals}";
        } else {
            $columns = $this->_prepareSQL(3, $this->mObjectDestination);
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
     * @param int $pSmartCode 是否模糊匹配
     *
     * @return bool
     */
    public function _update ($pSmartCode = 0)
    {
        if (! empty($this->mObjectSource)) {
            $ObjectSource = &$this->mObjectSource;
        } else {
            $ObjectSource = &$this;
        }
        if (empty($this->mObjectSource) && empty($this->mObjectDestination)) {
            $condition_array = $this->_prepareSQL(2, $this->mObjectSource);
        } else {
            $condition_array = $this->_prepareSQL($pSmartCode, $this->mObjectSource);
        }
        if (! empty($ObjectSource->mAdditionalCondition)) {
            array_push($condition_array, "({$ObjectSource->mAdditionalCondition})");
        }
        if (count($condition_array) > 0) {
            $condition = " WHERE " . implode(" and ", $condition_array);
        }
        $condition = $this->getCondition($pSmartCode, $ObjectSource);
        $equals = implode(",", $this->_prepareSQL(0, $this->mObjectDestination));
        $sql = "UPDATE {$this->mTableHash['name']} SET {$equals}{$condition}";
        if ($this->SqlCommand->ExecuteNonQuery($sql)) {
            $this->setState('object updated');
            $this->notifyObservers();
            return true;
        }
        return false;
    }
    public function _updateSelfByPrimaryKey ()
    {
        ;
    }
    /**
     * 刷新方法，如果存在符合条件的记录，则更新相应字段，否则，插入一条新的记录，必须有主键存在且主键已赋值
     *
     * @return void
     */
    public function _refresh ()
    {
        $keys = array_keys($this->mMapHash);
        foreach ($keys as $key) {
            if (! in_array($key, $this->mTableHash['key'])) {
                $mMapHash[$key] = $this->mMapHash[$key];
                $this->mMapHash[$key] = null;
            }
        }
        if ($this->_select()) {
            $this->mMapHash = array_merge($this->mMapHash, $mMapHash);
            $this->_update();
        } else {
            $this->mMapHash = array_merge($this->mMapHash, $mMapHash);
            $this->_insert();
        }
    }
    /**
     * 删除当前对象对应的记录
     *
     * @param int $pSmartCode 是否精确匹配,默认为精确匹配
     *
     * @return bool
     */
    public function _delete ($pSmartCode = 0)
    {
        $sql = "DELETE FROM {$this->mTableHash['name']}" . $this->getCondition($pSmartCode, $this);
        $return = $this->SqlCommand->ExecuteNonQuery($sql);
        $this->setState('object deleted');
        $this->notifyObservers();
        return $return;
    }
    /**
     * 从对应表中选取对应的一条记录
     *
     * @param int    $pLimit 偏移量，1为第一个
     * @param string $pOrder 排序字段
     *
     * @return bool
     */
    public function _select ($pLimit = 1, $pOrder = null)
    {
        $smart_code = ($pLimit > 1) ? 1 : 0;
        $result = $this->_list($pLimit, 1, $pOrder, $smart_code);
        if (count($result) > 0) {
            return $this->_fill($result[0]);
        }
        return false;
    }
    public function _loadByPrimaryKey ($pValue)
    {
        ;
    }
    /**
     * 把输入数组填充到当前对象中
     *
     * @param array &$pArray 需要填充的数组
     *
     * @return bool
     */
    function _fill (&$pArray)
    {
        if (is_array($pArray) && count($pArray) > 0) {
            foreach ($pArray as $key => $value) {
                if (array_key_exists($key, $this->mMapHash)) {
                    $this->mMapHash[$key] = $value;
                }
            }
            return true;
        }
        return false;
    }
    /**
     * 以数组形式返回符合条件的记录
     *
     * @param int    $pPage      页号,0为不分页
     * @param int    $pPageSize  每页多少条记录
     * @param mix    $pOrder     排序字段
     * @param int    $pSmartCode 是否模糊匹配
     * @param string $pColumns   需要提取的字段
     * @param string $pGroupBy   需要合并的字段
     *
     * @return array
     */
    function _list ($pPage = 0, $pPageSize = 10, $pOrder = null, $pSmartCode = 1, $pColumns = '*', $pGroupBy = '')
    {
        if ($pPage != 0 && $pPageSize != 1) {
            $count = $this->_count($pSmartCode, $pColumns, $pGroupBy);
            $this->mPagination = new KISS_Util_Pagination($pPage, $pPageSize, $count);
        }
        if (! ($pPage > 0 && $pPageSize == 1) && empty($pOrder) && count($this->mTableHash['key']) > 0) {
            $pOrder = implode(",", $this->mTableHash['key']);
        }
        $this->_generateSql($pOrder, $pSmartCode, $pColumns, $pGroupBy);
        //$this->_updateTableStatus();
        if (KISS_Framework_Config::isCached()) {
            return $this->SqlCommand->ExecuteCacheArrayQuery($this->mLastSQLQuery, $pPage, $pPageSize, 'assoc', array(
                $this->mTableHash['name']));
        } else {
            return $this->SqlCommand->ExecuteArrayQuery($this->mLastSQLQuery, $pPage, $pPageSize, 'assoc');
        }
    }
    /**
     * 以迭代器形式返回符合条件的记录
     *
     * @param int    $pPage      页号,0为不分页
     * @param int    $pPageSize  每页多少条记录
     * @param mix    $pOrder     排序字段
     * @param int    $pSmartCode 是否模糊匹配
     * @param string $pColumns   需要提取的字段
     * @param string $pGroupBy   需要合并的字段
     *
     * @return iterator
     */
    function _list_as_iterator ($pPage = 0, $pPageSize = 10, $pOrder = null, $pSmartCode = 1, $pColumns = '*', $pGroupBy = '')
    {
        if ($pPage != 0 && $pPageSize != 1) {
            $count = $this->_count($pSmartCode, $pColumns, $pGroupBy);
            //$this->mPagination = &new Pagination(1,1);
            $this->mPagination = new Pagination($pPage, $pPageSize, $count);
        }
        if (! ($pPage > 0 && $pPageSize == 1) && empty($pOrder) && count($this->mTableHash['key']) > 0) {
            $pOrder = implode(",", $this->mTableHash['key']);
        }
        $this->_generateSql($pOrder, $pSmartCode, $pColumns, $pGroupBy);
        //$this->_updateTableStatus();
        if (KISS_Framework_Config::isCached()) {
            return $this->SqlCommand->ExecuteCacheArrayQuery($this->mLastSQLQuery, $pPage, $pPageSize, 'assoc', array(
                $this->mTableHash['name']));
        } else {
            return $this->SqlCommand->ExecuteIteratorQuery($this->mLastSQLQuery, $pPage, $pPageSize, 'assoc');
        }
    }
    /**
     * 获得分页对象
     *
     * @return KISS_Util_Pagination
     */
    function _getPagination ()
    {
        return $this->mPagination;
    }
    /**
     * 以对象数组形式返回符合条件的记录
     *
     * @param int    $pPage      页号,0为不分页
     * @param int    $pPageSize  每页多少条记录
     * @param mix    $pOrder     排序字段
     * @param int    $pSmartCode 是否模糊匹配
     * @param string $pColumns   需要提取的字段
     * @param string $pGroupBy   需要合并的字段
     *
     * @return array
     */
    function _getObjects ($pPage = 0, $pPageSize = 10, $pOrder = null, $pSmartCode = 1, $pColumns = '*', $pGroupBy = '')
    {
        $arrays = $this->_list($pPage, $pPageSize, $pOrder, $pSmartCode, $pColumns, $pGroupBy);
        $name = get_class($this);
        $return = array();
        for ($i = 0; $i < count($arrays); $i ++) {
            $return[$i] = new $name();
            $return[$i]->_fill($arrays[$i]);
        }
        return $return;
    }
    /**
     * 更新当前表状态
     *
     * @return void
     */
    private function _updateTableStatus ()
    {
        $sql = "SHOW TABLE STATUS LIKE '{$this->mTableHash['name']}'";
        $this->mTableStatus = $this->SqlCommand->ExecuteArrayQuery($sql);
    }
    /**
     * 得到符合条件的记录总数
     *
     * @param int    $pSmartCode 是否精确匹配，1为模糊(默认),0为精确
     * @param string $pColumns   需要提取的字段
     * @param string $pGroupBy   需要合并的字段
     *
     * @return int
     */
    public function _count ($pSmartCode = 1, $pColumns = '*', $pGroupBy = '')
    {
        $this->_generateSql('', $pSmartCode, $pColumns, $pGroupBy);
        if (KISS_Framework_Config::isCached()) {
            $return = $this->SqlCommand->ExecuteCacheArrayQuery($this->mLastCountSQLQuery, 0, 10, 'num', array(
                $this->mTableHash['name']));
        } else {
            $return = $this->SqlCommand->ExecuteArrayQuery($this->mLastCountSQLQuery, 0, 10, 'num');
        }
        return $return[0][0];
    }
    /**
     * 重置当前对象，把当前对象与表字段对应的值全部置为null
     *
     * @return void
     */
    public function _reset ()
    {
        $keys = array_keys($this->mMapHash);
        $length = count($keys);
        for ($i = 0; $i < $length; $i ++) {
            $this->mMapHash[$keys[$i]] = null;
        }
    }
    /**
     * 生成SQL语句的条件子句
     *
     * @param int                      $pSmartCode 是否精确匹配，1为模糊(默认),0为精确
     * @param KISS_ORM_BaseTableObject $pObject    源对象实例
     *
     * @return string
     */
    function getCondition ($pSmartCode, $pObject)
    {
        if (function_exists('array_intersect_key')) {
            $array = array_intersect_key($this->mMapHash, array_flip($this->mTableHash['key']));
        } else {
            $keys = array_intersect(array_keys($this->mMapHash), $this->mTableHash['key']);
            foreach ($keys as $key) {
                $array[$key] = $this->mMapHash[$key];
            }
        }
        if (array_reduce($array, create_function('$a,$b', 'return ($a & !is_null($b));'), true)) {
            $condition_array = $this->_prepareSQL(2, $pObject);
        } else {
            $condition_array = $this->_prepareSQL($pSmartCode, $pObject);
            if (! empty($pObject->mAdditionalCondition)) {
                array_push($condition_array, "({$pObject->mAdditionalCondition})");
            }
        }
var_dump($condition_array);
        if (count($condition_array) > 0) {
            return " WHERE " . implode(" and ", $condition_array);
        }
        return '';
    }
    /**
     * implementation for ArrayAccess interface
     *
     * @param string $key Key
     *
     * @return bool
     */
    function offsetExists ($key)
    {
        return key_exists($key, $this->mMapHash);
    }
    /**
     * implementation for ArrayAccess interface
     *
     * @param string $key Key
     *
     * @return string
     */
    function offsetGet ($key)
    {
        return $this->mMapHash[$key];
    }
    /**
     * implementation for ArrayAccess interface
     *
     * @param string $key   Key
     * @param string $value Value
     *
     * @return void
     */
    function offsetSet ($key, $value)
    {
        $this->mMapHash[$key] = $value;
    }
    /**
     * implementation for ArrayAccess interface
     *
     * @param string $key Key
     *
     * @return void
     */
    function offsetUnset ($key)
    {
        unset($this->mMapHash[$key]);
    }
    /**
     * implementation for IteratorAggregate interface
     *
     * @return ArrayIterator
     */
    function getIterator ()
    {
        return new ArrayIterator($this->mMapHash);
    }
    /**
     * 实现观察者模式
     *
     * @return void
     */
    function notifyObservers ()
    {
        $observers = count($this->_observers);
        for ($i = 0; $i < $observers; $i ++) {
            $this->_observers[$i]->update($this);
        }
    }
    /**
     * 实现观察者模式
     *
     * @param object &$observer 观察者
     *
     * @return void
     */
    function addObserver (&$observer)
    {
        $this->_observers[] = & $observer;
    }
    /**
     * 实现观察者模式
     *
     * @return mix
     */
    function getState ()
    {
        return $this->_state;
    }
    /**
     * 实现观察者模式
     *
     * @param object $state 当前状态
     *
     * @return void
     */
    function setState ($state)
    {
        $this->_state = $state;
    }
    /**
     * 设置条件对象属性：不改版原始对象，条件属性设置到mObjectDestination
     *
     * @param string $pProperty 属性名，是属性对应的字段名
     * @param mixed  $pValue    属性值
     *
     * @return void
     */
    protected function setDestinationProperty ($pProperty, $pValue)
    {
        if (! isset($this->mObjectDestination) || ! is_object($this->mObjectDestination)) {
            $class_name = get_class($this);
            $this->mObjectDestination = new $class_name();
        }
        $this->mObjectDestination->mMapHash[$pProperty] = $pValue;
    }
    /**
     * 设置条件对象属性：不改版原始对象，条件属性设置到mObjectSource
     *
     * @param string $pProperty 属性名，是属性对应的字段名
     * @param mixed  $pValue    属性值
     *
     * @return void
     */
    protected function setConditionProperty ($pProperty, $pValue)
    {
        if (isset($this->mObjectSource) && ! is_object($this->mObjectSource)) {
            $class_name = get_class($this);
            $this->mObjectSource = new $class_name();
        }
        $this->mObjectSource->mMapHash[$pProperty] = $pValue;
    }
}
?>
