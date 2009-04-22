<?php
class KISS_Data_DataSet_Schema_XML {
    static $schema;
    function init() {
        if (is_null(self::$schema)) {
            $schema = simplexml_load_file('database.xml');
            self::$schema = self::readTables($schema->table);
        }
        return self::$schema;
    }

    function readTables($config) {
        $tables = array();
        foreach ($config as $table){
            $name = (string)$table['name'];
            $columns = self::readColumns($table->column);
            $keys = self::readKeys($table->key);
            $tables[$name] = array('columns'=>$columns, 'keys'=>$keys);
        }
        return $tables;
    }

    function readColumns($config) {
        $conums = array();
        foreach ($config as $column) {
            $name = (string)$column['name'];
            $type = (string)$column['type'];
            $conums[$name] = array('type' => $type);
        }
        return $conums;
    }

    function readKeys($config) {
        $keys = array('primary'=>array(), 'foreign'=>array());
        foreach ($config as $key) {
            $column = (string)$key['column'];
            $table = (string)$key['table'];
            $type = (string)$key['type'];
            $keys[$type][$table] = array('type' => $type, 'table' => $table, 'column' => $column);
        }
        return $keys;
    }
}
?>