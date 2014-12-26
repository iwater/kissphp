<?php
class KISS_SQLObject {
    const SQL_INSERT = 'INSERT INTO %s (%s) VALUES (%s)';
    const SQL_UPDATE = 'UPDATE %s SET %s WHERE %s';
    const SQL_DELETE = 'DELETE FROM %s WHERE %s';
    const SQL_SELECT = 'SELECT %s FROM %s WHERE %s';

    function getFetchQuery($pTable, $pData) {
        foreach ($pData as $key=>$value){
            $condition[] = "`{$key}` = $value";
        }
        $sql = sprintf(self::SQL_SELECT, '*', $pTable, implode(' and ', $condition));

        //$sqlcommand = SqlCommand::getInstance(0);
        //return $sqlcommand->ExecuteArrayQuery($sql);
        $db = new PDO('mysql:host=127.0.0.1;dbname=test','web','develop');
        $result = $db->query($sql);
        while ($return[] = $result->fetch(PDO_FETCH_ASSOC)) {
        }
        return $return;
    }

    function _delete(KISS_DataObject $pKDO) {
        $keys = $pKDO->get_keys();
        foreach ($keys['primary'] as $key => $value) {
            $sub_sql[] = "{$value['column']}={$pKDO->$value['column']}";
        }
        $sql = sprintf(self::SQL_DELETE, 'student', implode(' and ', $sub_sql));
        $db = new PDO('mysql:host=127.0.0.1;dbname=test','web','develop');
    }

    function _insert(KISS_DataObject $pKDO) {
        $columns = $pKDO->get_columns();
        foreach ($columns as $key => $value) {
            if (!is_null($pKDO->$key)) {
                $sub_sql[] = $pKDO->$key;
                $sub_sql1[] = $key;
                $sub_sql2[] = ':'.$key;
            }
        }
        $sql = sprintf(self::SQL_INSERT, 'student', implode(',', $sub_sql1), implode(',', $sub_sql2));

        $db = new PDO('mysql:host=127.0.0.1;dbname=test','web','develop');
        $stmt = $db->prepare($sql);
        foreach ($columns as $key => $value) {
            if (!is_null($pKDO->$key)) {
                $stmt->bindParam($key, $pKDO->$key);
            }
        }
        $stmt->execute();
        return $db->lastInsertId();
    }

    function _update(KISS_DataObject $pKDO) {
        $keys = $pKDO->get_keys();
        foreach ($keys['primary'] as $key => $value) {
            $sub_sql[] = "{$value['column']}=:{$value['column']}";
        }
        $columns = $pKDO->get_columns();
        foreach ($columns as $key => $value) {
            $sub_sql1[] = "{$key}=:{$key}";
        }
        $sql = sprintf(self::SQL_UPDATE, 'student', implode(',', $sub_sql1), implode(' and ', $sub_sql));
        $db = new PDO('mysql:host=127.0.0.1;dbname=test','web','develop');
        $stmt = $db->prepare($sql);
        foreach ($columns as $key => $value) {
                $stmt->bindParam($key, $pKDO->$key);
        }
        Reflection::export(new ReflectionObject($stmt));
        //$stmt->execute();
    }

    function _save(KISS_DataObject $pKDO) {
        $columns = $pKDO->get_columns();
        return (self::_checkKeys($pKDO)?self::_update($pKDO):self::_insert($pKDO));
    }

    function _checkKeys(KISS_DataObject $pKDO) {
        $keys = $pKDO->get_keys();
        foreach ($keys['primary'] as $key => $value) {
            if (is_null($pKDO->$value['column'])) {
                return false;
            }
        }
        return true;
    }
}
?>