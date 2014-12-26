<?php
class KISS_DataObject {
    private $table;
    private $data = array();
    //public $property = array();
    public static $objects = array();
    public $object_id = 0;
    function __construct($table, $keys =array()) {
        $this->object_id = uniqid();
        $schema = KISS_Data_DataSet_Schema_XML::init();
        $this->table = $table;
        if (count($keys) > 0) {
            $this->_select($keys);
        }
        $values = array_values($keys);
        KISS_DataObject::$objects[$table][$values[0]] = $this;
    }

    function __get($pKey) {
        $schema = KISS_Data_DataSet_Schema_XML::init();
        //¼ì²éÍâ¼ü
        $table = $this->table;
        $foreign_keys = &$schema[$this->table]['keys']['foreign'];
        if (array_key_exists($pKey,$schema) && array_key_exists($pKey, $foreign_keys)) {
            if (!isset($this->data[$pKey]) || is_null($this->data[$pKey])) {
                $code = "\$this->data['{$pKey}'] = $pKey::find(\$this->get_column('{$foreign_keys[$pKey]['column']}'));";
                eval($code);
            }
            return $this->data[$pKey];
        }
        elseif (array_key_exists($pKey,$schema) && array_key_exists($this->table, $schema[$pKey]['keys']['foreign'])) {
            if (!isset($this->data[$pKey]) || is_null($this->data[$pKey])) {
                $results = KISS_SQLObject::getFetchQuery($pKey,array($schema[$pKey]['keys']['foreign'][$this->table]['column'] => $this->get_column($schema[$pKey]['keys']['foreign'][$this->table]['column'])));
                foreach ($results as $result) {
                    $code = "\$this->data['{$pKey}'][] = {$pKey}::find('{$result['student_id']}');";
                    eval($code);
                }
            }
            return $this->data[$pKey];
        } elseif (array_key_exists($pKey, $this->data)) {
            return $this->data[$pKey];
        }
    }

    private function has_column($pKey) {
        $schema = KISS_Data_DataSet_Schema_XML::init();
        return array_key_exists($pKey, $schema[$this->table]['columns']);
    }

    private function set_column($pKey, $pValue) {
        if ($this->has_column($pKey)) {
            $this->data[$pKey] = $pValue;
        }
    }

    public function get_columns() {
        $schema = KISS_Data_DataSet_Schema_XML::init();
        return $schema[$this->table]['columns'];
    }
    
    public function get_keys() {
        $schema = KISS_Data_DataSet_Schema_XML::init();
        return $schema[$this->table]['keys'];
    }
    
    private function get_column($pKey) {
        if ($this->has_column($pKey)) {
            return $this->data[$pKey];
        }
    }

    private function _select($keys) {
        $result = KISS_SQLObject::getFetchQuery($this->table, $keys);
        $this->_fill($result[0]);
    }
    
    public function _save() {
        return KISS_SQLObject::_save($this);
    }
    
    public function _delete() {
        if (KISS_SQLObject::_delete($this)) {
            $this->_reset();
            return true;
        }
        return false;
    }
    
    public function _reset() {
    }

    private function _fill($pData) {
        foreach ($pData as $key=>$value){
            $this->set_column($key, $value);
        }
    }

    function __sleep() {
        $this->property = array();
        $schema = KISS_Data_DataSet_Schema_XML::init();
        foreach ($schema[$this->table]['columns'] as $key => $value) {
            $this->property[$key] = $this->data[$key];
        }
        $this->property['_table'] = $this->table;
        return array('property');
    }

    function __wakeup() {
        $schema = KISS_Data_DataSet_Schema_XML::init();
        foreach ($schema[$this->property['_table']]['columns'] as $key => $value) {
            $this->data[$key] = $this->property[$key];
        }
        $this->table = $this->property['_table'];
        $this->property = array();
    }

    static function find($pTable, $pPrimaryKey) {
        if (isset(KISS_DataObject::$objects[$pTable][$pPrimaryKey]))
        return KISS_DataObject::$objects[$pTable][$pPrimaryKey];
        else
        return new $pTable($pPrimaryKey);
    }
}
?>