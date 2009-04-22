<?php
/**
* @author бМлн <matao@bj.tom.com>
* @version v 1.0 2004/04/09
* @package Core_Class
*/

class KISS_Util_DBSession {
    var $dbLink;
    function open() {
        global $session_database_config;
        if (!($this->dbLink = mysql_connect("{$session_database_config['host']}:{$session_database_config['port']}", $session_database_config['user'], $session_database_config['pass']))) {
            return(false);
        }
        if (!mysql_select_db (substr($session_database_config['path'], 1), $this->dbLink)) {
            return(false);
        }
        return(true);
    }

    function close() {
        $this->open();
        mysql_close($this->dbLink);
        return(true);
    }

    function read($id) {
        $this->open();
        $Query = "SELECT SessionData " . "FROM session " . "WHERE ID = '" . addslashes($id) . "'";
        if (!($dbResult = mysql_query($Query, $this->dbLink))) {
            return(false);
        }
        $dbRow = mysql_fetch_assoc($dbResult);
        $Query = "UPDATE session " . "SET " . "LastAction=NOW() " . "WHERE ID='" . addslashes($id) . "' ";
        if (!($dbResult = mysql_query($Query, $this->dbLink))) {
            return(false);
        }
        return($dbRow['SessionData']);
    }

    function write($id, $data) {
        $this->open();
        $Query = "INSERT IGNORE " . "INTO session (ID) " . "VALUES ('" . addslashes($id) . "')";
        if (!($dbResult = mysql_query($Query, $this->dbLink))) {
            return(false);
        }
        $Query = "UPDATE session " . "SET " . "SessionData='" . addslashes($data) . "', " . "LastAction=NOW() " . "WHERE ID='" . addslashes($id) . "' ";
        if (!($dbResult = mysql_query($Query, $this->dbLink))) {
            return(false);
        }
        return(true);
    }

    function destroy($id) {
        $this->open();
        $Query = "DELETE FROM session " . "WHERE ID='" . addslashes($id) . "' ";
        if (!($dbResult = mysql_query($Query, $this->dbLink))) {
            return(false);
        }
        return(true);
    }

    function garbage($lifetime) {
        return(true);
    }
}
?>