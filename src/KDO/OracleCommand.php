<?php
class KISS_KDO_OracleCommand extends KISS_KDO_SqlCommand
{
    private $skip = 0;
    private $maxrows = - 1;
    private $mResultType = array('assoc'=>OCI_ASSOC,'num'=>OCI_NUM, 'both'=>OCI_BOTH);
    function db_connect ()
    {
        $connection = oci_connect($this->mDatabaseUsername, $this->mDatabasePassword, "//{$this->mDatabaseHost}:{$this->mDatabasePort}/{$this->mDatabaseName}", "zhs16gbk");
        if (! is_resource($connection)) {
            die('数据库访问错误！');
        }
        return $connection;
    }
    public function db_query ($pQuery)
    {
        $stmt = oci_parse($this->mLink, $pQuery);
        oci_execute($stmt);
        return $stmt;
    }
    function getTableFieldHash ($pTable)
    {
    }
    function getTablePrimeKey ($pTable)
    {
    }
    public function db_fetch_all ($result, $pResultType = 'both')
    {
        oci_fetch_all($result, $results, $this->skip, $this->maxrows, OCI_FETCHSTATEMENT_BY_ROW);
        return $results;
    }
    public function db_insert_id ()
    {
    }
    public function db_affected_rows ($pResult)
    {
    }
    public function db_num_rows ($pResult)
    {
    }
    public function db_fetch_array ($pResult, $pResultType = 'both')
    {
        return oci_fetch_array($pResult, $this->mResultType[$pResultType]);
    }
    public function db_free_result ($pResult)
    {
        oci_free_statement($pResult);
    }
    public function db_close ()
    {
        oci_close($this->mLink);
    }
    public function db_data_seek ($result_identifier, $row_number)
    {
    }
    public function PreparePagedArrayQuery ($sql, $pPageNo = 0, $pPageSize = -1)
    {
        //$this->skip = ($pPageNo-1)*$pPageSize;
        //$this->maxrows = $pPageSize;
        $sql = "select TB.* FROM (select rownum rn,TA.* from ({$sql})TA where rownum<=".($pPageNo*$pPageSize).")TB where rn>".(($pPageNo-1)*$pPageSize);
        return $this->ExecuteQuery($sql);
    }
}
?>