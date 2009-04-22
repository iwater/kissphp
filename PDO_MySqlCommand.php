<?php
/**
*
* @author 马涛 <matao@bj.tom.com>
* @version v 2.1 alpha 2004/10/27
* @package Core_Class
*/

/**
* MySQL 的数据库抽象层，优化性能，使用了 Singleton 设计模式
*/
class KISS_PDO_MySqlCommand extends SqlCommand {
    const DB_PORT = 3306;
    private $connect;

    private $mResultType = array('assoc'=>MYSQL_ASSOC,'num'=>MYSQL_NUM, 'both'=>MYSQL_BOTH);

    function getTableFieldHash($pTable) {
        $this->connectDB();
        $fields = mysql_list_fields($this->mDatabaseName, $pTable, $this->mLink);
        $columns = mysql_num_fields($fields);
        for ($i = 0; $i < $columns; $i++) {
            $field_name = mysql_field_name($fields, $i);
            $return[$field_name]['name'] = $field_name;
            $return[$field_name]['type'] = mysql_field_type($fields, $i);
            $return[$field_name]['length'] = mysql_field_len($fields, $i);
            $return[$field_name]['flags'] = explode(" ", mysql_field_flags($fields, $i));
        }
        return $return;
    }

    function getTablePrimeKey($pTable) {
        $this->connectDB();
        $fields = mysql_list_fields($this->mDatabaseName, $pTable, $this->mLink);
        $columns = mysql_num_fields($fields);
        for ($i = 0; $i < $columns; $i++) {
            $field_name = mysql_field_name($fields, $i);
            if (in_array("primary_key", explode(" ", mysql_field_flags($fields, $i)))) {
                $return[] = $field_name;
            }
        }
        return $return;
    }

    function db_connect () {
        try {
            $this->connection = new PDO("mysql:host={$this->mDatabaseHost};dbname={$this->mDatabaseName}", $this->mDatabaseUsername, $this->mDatabasePassword);
            return $this->connection;
        }
        catch (PDOException $error) {
            die('数据库访问错误！');
        }
        return $this->connection;
    }

    public function db_num_rows($pResult) {
    }

    public function db_fetch_array($pResult ,$pResultType = 'both') {
        $this->connection->fetch();
        return mysql_fetch_array($pResult, $this->mResultType[$pResultType]);
    }

    public function db_free_result($pResult) {
        return mysql_free_result($pResult);
    }

    public function db_query($pQuery) {
        if (count(SqlCommand::$theInstances) > 1) {
            mysql_select_db ($this->mDatabaseName, $this->mLink);
        }
        $return = mysql_query($pQuery, $this->mLink);
        if(mysql_errno()) {
            die( "数据库错误：{$pQuery}");
        }
        return $return;
    }

    public function db_close() {
        unset($this->connection);
    }

    public function db_insert_id() {
        return $this->connection->lastInsertId();
    }

    public function db_affected_rows($pResult) {
        return $this->connection->rowCount();
    }
    
    public function db_data_seek($result_identifier, $row_number) {
    }

    function PreparePagedArrayQuery ($sql, $pPageNo=0, $pPageSize = 10) {
        $sql .= " limit ".(($pPageNo - 1) * $pPageSize).",".$pPageSize;
        return $this->ExecuteQuery ($sql);
    }
}
?>