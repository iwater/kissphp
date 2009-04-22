<?php
/**
*
* @author 马涛 <matao@bj.tom.com>
* @version v 2.1 alpha 2004/10/27
* @package Core_Class
*/
/**
* SQL Server 的数据库抽象层
*/
class KISS_KDO_MsSqlCommand extends KISS_KDO_SqlCommand {
    
    private $mResultType = array('assoc'=>MSSQL_ASSOC,'num'=>MSSQL_NUM, 'both'=>MSSQL_BOTH);

    function db_connect () {
        if (!empty($this->mDatabasePort)) {
            $connection = mssql_connect ("{$this->mDatabaseHost},{$this->mDatabasePort}", "{$this->mDatabaseUsername}", "{$this->mDatabasePassword}");
        }
        else {
            $connection = mssql_connect ("{$this->mDatabaseHost}", "{$this->mDatabaseUsername}", "{$this->mDatabasePassword}");
        }
        mssql_select_db ($this->mDatabaseName, $connection);
        return $connection;
    }

    public function db_num_rows($pResult) {
        return mssql_num_rows($pResult);
    }
    
    public function db_fetch_array($pResult ,$pResultType = 'both') {
        return mssql_fetch_array($pResult, $this->mResultType[$pResultType]);
    }

    public function db_free_result($pResult) {
        return mssql_free_result($pResult);
    }

    public function db_query($pQuery) {
        if (count(SqlCommand::$theInstances) > 1) {
            mssql_select_db ($this->mDatabaseName, $this->mLink);
        }
        return mssql_query($pQuery, $this->mLink);
    }

    public function db_close() {
        return mssql_close ($this->mLink);
    }

    function getTableFieldHash($pTable) {
        $return = array();
        $temp = $this->ExecuteArrayQuery("sp_columns {$pTable}", 'assoc');
        for($i=0; $i<count($temp); $i++) {
            $return[$temp[$i]['COLUMN_NAME']]['name'] = $temp[$i]['COLUMN_NAME'];
            $return[$temp[$i]['COLUMN_NAME']]['type'] = $temp[$i]['TYPE_NAME'];
            if(in_array($return[$temp[$i]['COLUMN_NAME']]['type'],array('varchar','char','nvarchar','nchar','text'))) {
                $return[$temp[$i]['COLUMN_NAME']]['type'] = 'string';
            }
            $return[$temp[$i]['COLUMN_NAME']]['length'] = $temp[$i]['LENGTH'];
        }
        return $return;
    }
    
    function getTablePrimeKey($pTable) {
        $return = array();
        $temp = $this->ExecuteArrayQuery("sp_pkeys {$pTable}");
        for($i=0; $i<count($temp); $i++) {
            $return[$i] = $temp[$i]['COLUMN_NAME'];
        }
        return $return;
    }

    public function db_affected_rows($pResult) {
        return mssql_rows_affected($this->mLink);
    }
    
    function db_insert_id() {
        $sql = "select @@IDENTITY";
        return $this->ExecuteScalar($sql);
    }

    function PreparePagedArrayQuery ($sql, $pPageNo=0, $pPageSize = 10) {
        $str = "select top ".($pPageSize * $pPageNo)." ";
        $s1 = trim($sql);
        $newsql = $str.substr($s1, 6);
        $result = $this->ExecuteQuery ($newsql);
        if($pPageNo > 1){
            $this->db_data_seek($result,($pPageNo - 1) * $pPageSize);
        }
        return $result;
    }
    
    public function db_data_seek($result_identifier, $row_number) {
        return mssql_data_seek($result_identifier, $row_number);
    }
}
?>