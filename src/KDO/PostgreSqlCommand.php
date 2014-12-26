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
class KISS_KDO_PostgreSqlCommand extends KISS_KDO_SqlCommand {
    const DB_PORT = 5432;

    private $mResultType = array('assoc'=>PGSQL_ASSOC,'num'=>PGSQL_NUM, 'both'=>PGSQL_BOTH);

    function getTableFieldHash($pTable) {
        $sql = "SELECT a.attname, format_type(t.oid, null) as typname,pg_catalog.col_description(a.attrelid, a.attnum) AS comment
                  FROM pg_namespace n, pg_class c, 
                       pg_attribute a, pg_type t
                  WHERE n.oid = c.relnamespace
                    and c.relkind = 'r'     -- no indices
                    and n.nspname not like 'pg\\_%' -- no catalogs
                    and n.nspname != 'information_schema' -- no information_schema
                    and a.attnum > 0        -- no system att's
                    and not a.attisdropped   -- no dropped columns
                    and a.attrelid = c.oid
                    and a.atttypid = t.oid
                    and c.relname = '{$pTable}'
                  ORDER BY a.attnum";
        $columns = $this->ExecuteArrayQuery($sql);
        foreach ($columns as $column) {
            $return[$column['attname']]['name'] = $column['attname'];
            $return[$column['attname']]['type'] = self::convertToPHPType($column['typname']);
            $return[$column['attname']]['length'] = 0;
            $return[$column['attname']]['flags'] = array();
            $return[$column['attname']]['comment'] = $column['comment'];;
        }
        return $return;
    }

    function getTablePrimeKey($pTable) {
        $sql = "SELECT pg_catalog.pg_get_indexdef(i.indexrelid, 0, true) AS inddef
                FROM pg_catalog.pg_class c, pg_catalog.pg_class c2, pg_catalog.pg_index i
                WHERE c.relname = '{$pTable}' AND pg_catalog.pg_table_is_visible(c.oid) AND i.indisprimary = 't'
                AND c.oid = i.indrelid AND i.indexrelid = c2.oid
                ORDER BY c2.relname";
        $return = $this->ExecuteScalar($sql);
        preg_match('/\((.*)\)/i', $return, $matchs);
        return array_map('trim', explode(',', $matchs[1]));
    }

    function db_connect () {
        $uri = "host={$this->mDatabaseHost} port={$this->mDatabasePort} dbname={$this->mDatabaseName} user={$this->mDatabaseUsername} password={$this->mDatabasePassword}";
        $connection = pg_connect($uri);
        if (pg_connection_status($connection) == PGSQL_CONNECTION_OK) {
            return $connection;
        }
        die('数据库访问错误！');
    }

    public function db_num_rows($pResult) {
        return pg_num_rows($pResult);
    }

    public function db_fetch_array($pResult ,$pResultType = 'both') {
        return @pg_fetch_array($pResult, null, $this->mResultType[$pResultType]);
    }

    public function db_free_result($pResult) {
        return @pg_free_result($pResult);
    }

    public function db_query($pQuery) {
        echo $pQuery."\n";
        return @pg_query($this->mLink, $pQuery);
    }

    public function db_close() {
        return pg_close($this->mLink);
    }

    public function db_insert_id() {
    }

    public function db_affected_rows($pResult) {
        return pg_affected_rows($this->$pResult);
    }

    public function db_data_seek($result_identifier, $row_number) {
        return pg_result_seek($result_identifier, $row_number);
    }

    function PreparePagedArrayQuery ($sql, $pPageNo=0, $pPageSize = 10) {
        $sql .= " limit {$pPageSize} OFFSET ".(($pPageNo - 1) * $pPageSize);
        return $this->ExecuteQuery ($sql);
    }

    function ExecuteInsertQuery ($sql) {
        preg_match('/insert\s+into\s+(\S+?)\s+\(/i', $sql, $mathch);
        $pk_name = $mathch[1].'_'.array_pop($this->getTablePrimeKey($mathch[1])).'_seq';
        $result = $this->ExecuteQuery ($sql);
        if (is_resource($result) && pg_affected_rows($result) == 1) {
            return $this->ExecuteScalar("SELECT currval('{$pk_name}')");
        }
        return 0;
    }
    
    static function convertToPHPType ($pType) {
        if (in_array($pType, array('integer'))) {
            return 'int';
        }
        return $pType;
    }
}
?>