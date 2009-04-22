<?php
/**
*
* @author 马涛 <matao@bj.tom.com>
* @version v 2.1 alpha 2004/10/27
* @package Core_Class
*/

/**
* 通用数据库抽象层，优化性能，使用了 Singleton 设计模式
*/
abstract class KISS_KDO_SqlCommand extends KISS_Object {
    public $mLink;
    public $mDatabaseHost;
    public $mDatabaseUsername;
    public $mDatabasePassword;
    public $mDatabaseName;
    public $mDatabasePort;

    private  $mQueryArray = array();
    private $uniqueKey;

    static $theInstances = array();

    /**
     * 返回一个数据库对象
     *
     * @param int $pDBConfig
     * @return KISS_KDO_SqlCommand
     */
    static function &getInstance($pDBConfig = 0) {
        if(empty(KISS_KDO_SqlCommand::$theInstances[$pDBConfig])) {
            $registry = &KISS_Framework_Registry::instance();
            $db_configs = $registry->getEntry('database_connections');
            $dbconfig = $db_configs[$pDBConfig];
            $class_name = 'KISS_KDO_'.$dbconfig['DatabaseType'].'Command';
            if (!class_exists($class_name)) {
                die('不支持该数据库类型：'.$class_name.'!<br>支持类型:MySql MsSql');
            }
            self::$theInstances[$pDBConfig] = new $class_name;
            self::$theInstances[$pDBConfig]->resetDB($db_configs[$pDBConfig]);
        }
        return self::$theInstances[$pDBConfig];
    }

    function __construct($pDBConfig = "") {
        parent::__construct();
    }

    function __destruct() {
        $this->closeDBForce();
        parent::__destruct();
    }

    function resetDB ($DBConfig) {
        $this->mDatabaseHost = $DBConfig['DatabaseHost'];
        $this->mDatabaseUsername = $DBConfig['DatabaseUsername'];
        $this->mDatabasePassword = $DBConfig['DatabasePassword'];
        $this->mDatabaseName = $DBConfig['DatabaseName'];
        $this->mDatabasePort = $DBConfig['DatabasePort'];

        $this->closeDBForce ();
    }

    function connectDB () {
        if(!is_resource($this->mLink)) {
            $this->mLink = $this->db_connect();
        }
    }

    function closeDBForce () {
        if(is_resource($this->mLink)) {
            $this->db_close();
        }
    }

    /**
     * 执行没有返回结果的SQL
     *
     * @param string $sql
     * @return bool
     */
    function ExecuteNonQuery ($sql) {
      return $this->ExecuteQuery ($sql);
    }

    /**
     * 执行插入SQL
     *
     * @param string $sql
     * @return int
     */
    function ExecuteInsertQuery ($sql) {
        $result = $this->ExecuteQuery ($sql);
        if ($result) {
            if($this->db_affected_rows($result)>0) {
                return $this->db_insert_id();
            }
            else {
                return 0;
            }
        }
        else {
            return 0;
        }
    }

    function ExecuteCountQuery ($pSql) {
        $count_sql = preg_replace("/select.+?\sfrom\s/i", "SELECT count(*) FROM ", $pSql);
        return $this->ExecuteScalar($count_sql);
    }

    function db_fetch_all($result, $pResultType) {
        $return = array();
        while ($return[] = $this->db_fetch_array($result, $pResultType)) {
        }
        $this->db_free_result($result);
        array_pop($return);
        return $return;
    }

    function ExecuteArrayQuery ($sql, $pPageNo=0, $pPageSize = 10, $pResultType = 'both') {
        if($pPageNo > 0) {
            $result = $this->PreparePagedArrayQuery($sql, $pPageNo, $pPageSize, $pResultType);
        }
        else {
            $result = $this->ExecuteQuery ($sql);
        }
        return $this->db_fetch_all($result, $pResultType);
    }

    function ExecuteIteratorQuery ($sql, $pPageNo=0, $pPageSize = 10, $pResultType = 'both') {
        if($pPageNo > 0) {
            $result = $this->PreparePagedArrayQuery($sql, $pPageNo, $pPageSize, $pResultType);
        }
        else {
            $result = $this->ExecuteQuery ($sql);
        }
        return new KISS_KDO_SqlCommandIterator($this, $result, $pResultType);
    }

    /**
    * 提供 Cache 缓冲的数据返回查询,由于要检测受影响的 Table 的修改时间,和更新 Cache 文件,会有一点的性能损失,适合耗时较大的查询
    */
    function ExecuteCacheArrayQuery($sql, $pPageNo=0, $pPageSize = 10, $pResultType = 'both', $pTableName) {
        /**
        * 查询受影响的 Table 的修改时间,确定 Cache 文件的名字
        */
        $status_update_time = "";
        for($i=0; $i<count($pTableName); $i++) {
            $status_sql = "SHOW TABLE STATUS LIKE '{$pTableName[$i]}'";
            $status = $this->ExecuteArrayQuery($status_sql);
            $status_update_time .= $status[0]['Update_time'];
        }

        /**
        * 检查 Cache 文件,如果存在就从 Cache 文件读取,否则再从数据库读取
        */
        if(Cache::haveSqlCache($sql,$status_update_time)) {
            return Cache::getSqlCache($sql,$status_update_time);
        }
        return $this->ExecuteArrayQuery($sql, $pPageNo, $pPageSize, $pResultType);
    }

    function ExecuteStringQuery ($sql) {
        die('Please Use:Util::Array2String()');
    }

    function ExecuteHashQuery ($sql) {
        die('Please Use:Util::Array2Hash()');
    }

    function ExecuteScalar ($sql) {
        $result = $this->ExecuteQuery ($sql);
        $return = 0;

        if ($row = $this->db_fetch_array($result, 'num')) {
            $return = $row[0];
        }
        $this->db_free_result($result);
        return $return;
    }

    /**
     * 执行SQL查询
     *
     * @param string $pQuery
     * @return mix
     */
    function ExecuteQuery ($pQuery) {
        $this->connectDB ();
        if (KISS_Framework_Config::getMode()=='debug') {
            KISS_Util_Debug::setDebugInfo(array($this->UniqueObjectID,get_class($this),'SQLQuery',$pQuery));
        }
        return $this->db_query($pQuery);
    }

    abstract function getTableFieldHash($pTable);

    abstract function getTablePrimeKey($pTable);

    public abstract function db_insert_id() ;

    public abstract function db_affected_rows($pResult);

    public abstract function db_num_rows($pResult) ;

    public abstract function db_fetch_array($pResult ,$pResultType = 'both');

    public abstract function db_free_result($pResult);

    /**
     * 执行SQL语句
     *
     * @param string $pQuery
     * @return mix
     */
    public abstract function db_query($pQuery);

    public abstract function db_connect();

    public abstract function db_close();

    public abstract function db_data_seek($result_identifier, $row_number);

    public abstract function PreparePagedArrayQuery ($sql, $pPageNo=0, $pPageSize = 10);
}
?>