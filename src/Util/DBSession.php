<?php
/**
 * KISS 核心类文件
 *
 * PHP versions 5
 *
 * LICENSE: This source file is subject to version 3.0 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_0.txt.  If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @category  Core
 * @package   KISS
 * @author    iwater <iwater@gmail.com>
 * @copyright 2003-2009 iwater
 * @license   http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version   SVN: <svn_id>
 * @link      http://www.kissphp.cn
 */

/**
 * KISS_Util_DBSession
 *
 * @category  Core
 * @package   KISS
 * @author    iwater <iwater@gmail.com>
 * @copyright 2003-2009 iwater
 * @license   http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version   Release: 3.5.0
 * @link      http://www.kissphp.cn
 */
class KISS_Util_DBSession
{
    var $dbLink;
    function open ()
    {
        global $session_database_config;
        if (! ($this->dbLink = mysql_connect("{$session_database_config['host']}:{$session_database_config['port']}", $session_database_config['user'], $session_database_config['pass']))) {
            return (false);
        }
        if (! mysql_select_db(substr($session_database_config['path'], 1), $this->dbLink)) {
            return (false);
        }
        return (true);
    }
    function close ()
    {
        $this->open();
        mysql_close($this->dbLink);
        return (true);
    }
    function read ($id)
    {
        $this->open();
        $Query = "SELECT SessionData " . "FROM session " . "WHERE ID = '" . addslashes($id) . "'";
        if (! ($dbResult = mysql_query($Query, $this->dbLink))) {
            return (false);
        }
        $dbRow = mysql_fetch_assoc($dbResult);
        $Query = "UPDATE session " . "SET " . "LastAction=NOW() " . "WHERE ID='" . addslashes($id) . "' ";
        if (! ($dbResult = mysql_query($Query, $this->dbLink))) {
            return (false);
        }
        return ($dbRow['SessionData']);
    }
    function write ($id, $data)
    {
        $this->open();
        $Query = "INSERT IGNORE " . "INTO session (ID) " . "VALUES ('" . addslashes($id) . "')";
        if (! ($dbResult = mysql_query($Query, $this->dbLink))) {
            return (false);
        }
        $Query = "UPDATE session " . "SET " . "SessionData='" . addslashes($data) . "', " . "LastAction=NOW() " . "WHERE ID='" . addslashes($id) . "' ";
        if (! ($dbResult = mysql_query($Query, $this->dbLink))) {
            return (false);
        }
        return (true);
    }
    function destroy ($id)
    {
        $this->open();
        $Query = "DELETE FROM session " . "WHERE ID='" . addslashes($id) . "' ";
        if (! ($dbResult = mysql_query($Query, $this->dbLink))) {
            return (false);
        }
        return (true);
    }
    function garbage ($lifetime)
    {
        return (true);
    }
}
?>