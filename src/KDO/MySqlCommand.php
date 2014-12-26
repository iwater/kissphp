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
class KISS_KDO_MySqlCommand extends KISS_KDO_SqlCommand {
	const DB_PORT = 3306;

	private $mResultType = array('assoc'=>MYSQL_ASSOC,'num'=>MYSQL_NUM, 'both'=>MYSQL_BOTH);

	function getTableFieldHash($pTable) {
		$this->connectDB();
                $columns = $this->ExecuteAssocArrayQuery('SHOW COLUMNS FROM '.$pTable);
		for ($i = 0; $i < count($columns); $i++) {
                        $field_name = $columns[$i]['Field'];
                        $flag = preg_split("/[\(\)\s,]/", $columns[$i]['Type']);
			$return[$field_name]['name'] = $field_name;
			$return[$field_name]['type'] = $flag[0];
			//$return[$field_name]['length'] = mysql_field_len($fields, $i);
			//$return[$field_name]['flags'] = explode(" ", mysql_field_flags($fields, $i));
		}
		return $return;
	}

	function getTablePrimeKey($pTable) {
		$this->connectDB();
                $columns = $this->ExecuteAssocArrayQuery('SHOW COLUMNS FROM '.$pTable);
		$return = array();
		for ($i = 0; $i < count($columns); $i++) {
			if ($columns[$i]['Key'] == 'PRI') {
				$return[] = $columns[$i]['Field'];
			}
		}
		return $return;
	}

	function db_connect () {
		$connection = mysql_connect ("{$this->mDatabaseHost}:{$this->mDatabasePort}", $this->mDatabaseUsername, $this->mDatabasePassword);
		if (!is_resource($connection)) {
			die('数据库访问错误！');
		}
		mysql_select_db ($this->mDatabaseName, $connection);
		@mysql_query("SET NAMES ".KISS_Application::$charset, $connection);
		return $connection;
	}

	public function db_num_rows($pResult) {
		return mysql_num_rows($pResult);
	}

	public function db_fetch_array($pResult ,$pResultType = 'both') {
		return mysql_fetch_array($pResult, $this->mResultType[$pResultType]);
	}

	public function db_free_result($pResult) {
		return mysql_free_result($pResult);
	}

	/**
	 * 执行SQL语句
	 *
	 * @param string $pQuery
	 * @return mix
	 */
	public function db_query($pQuery) {
		if (count(KISS_KDO_SqlCommand::$theInstances) > 1) {
			mysql_select_db ($this->mDatabaseName, $this->mLink);
		}
$start = microtime(true);
//file_put_contents('/dev/shm/temp/sql.log', $pQuery."\n", FILE_APPEND);
		$return = mysql_query($pQuery, $this->mLink);
$end = (microtime(true) - $start) * 1000;
//file_put_contents('/dev/shm/temp/sql2.log', "{$end}\t{$pQuery}\n", FILE_APPEND);
		if(mysql_errno($this->mLink)) {
		  throw new KISS_KDO_Exception($pQuery."\n".mysql_error($this->mLink), mysql_errno($this->mLink));
		}
		return $return;
	}

	public function db_close() {
		return mysql_close ($this->mLink);
	}

	public function db_insert_id() {
		return mysql_insert_id($this->mLink);
	}

	public function db_affected_rows($pResult) {
		return mysql_affected_rows($this->mLink);
	}

	public function db_data_seek($result_identifier, $row_number) {
		return mysql_data_seek($result_identifier, $row_number);
	}

	function PreparePagedArrayQuery ($sql, $pPageNo=0, $pPageSize = 10) {
		$sql .= " limit ".(($pPageNo - 1) * $pPageSize).",".$pPageSize;
		return $this->ExecuteQuery ($sql);
	}
}
?>
