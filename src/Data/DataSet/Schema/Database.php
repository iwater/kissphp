<?php
class KISS_Data_DataSet_Schema_Database {
    static $schema;
    private $link;
    function init() {
        if (is_null(self::$schema)) {
            self::$schema = self::readTables();
        }
        return self::$schema;
    }

    function readTables() {
        $link = mysql_connect('localhost', 'root', 'M2oNa3D9');
        $result = mysql_list_tables('test', $link);
        $tables = array();
        while ($row = mysql_fetch_row($result)) {
            $name = $row[0];
            $columns = self::readColumns(mysql_list_fields('album', $name, $link));
            $keys = self::readKeys(mysql_list_fields('album', $name, $link));
            $tables[$name] = array('columns'=>$columns, 'keys'=>$keys);
        }
        return $tables;
    }

    function readColumns($result) {
        $columns = array();
        $column_num = mysql_num_fields($result);
        for ($i = 0; $i < $column_num; $i++) {
            $name = mysql_field_name($result, $i);
            $type = mysql_field_type($result, $i);
            $conums[$name] = array('type' => $type);
        }
        return $conums;
    }

    function readKeys($result) {
        $keys = array('primary'=>array(), 'foreign'=>array());
        $column_num = mysql_num_fields($result);
        for ($i = 0; $i < $column_num; $i++) {
            $column = mysql_field_name($result, $i);
            $table = mysql_field_table($result, $i);
            if(strpos(mysql_field_flags($result, $i), 'primary_key') > 0) {
                $type = 'primary';
                $keys[$type][$table] = array('type' => $type, 'table' => $table, 'column' => $column);
            }
        }
        return $keys;
    }
}
?>