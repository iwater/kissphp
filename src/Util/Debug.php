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
 * KISS_Util_Debug
 *
 * @category  Core
 * @package   KISS
 * @author    iwater <iwater@gmail.com>
 * @copyright 2003-2009 iwater
 * @license   http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version   Release: 3.5.0
 * @link      http://www.kissphp.cn
 */
class KISS_Util_Debug
{
    private static $_info = array();
    private static $_theInstances;
    protected $UniqueObjectID = 'init';
    function __construct ()
    {
        $this->UniqueObjectID = uniqid();
    }
    function &getInstance()
    {
        if (is_null(self::$_theInstances)) {
            self::$_theInstances = new Debug();
        }
        return self::$_theInstances;
    }

    public function disableDebugInfoOutput ()
    {
        KISS_Framework_Config::setMode('online');
    }
    function getDumpInfo ()
    {
        $sql    = array();
        $object = array();
        $echo   = <<<EOF
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
    <title>KISS Debug Console</title>
{literal}
<style type="text/css">
/* <![CDATA[ */
body, h1, h2, td, th, p {
    font-family: sans-serif;
    font-weight: normal;
    font-size: 0.9em;
    margin: 1px;
    padding: 0;
}

h1 {
    margin: 0;
    text-align: left;
    padding: 2px;
    background-color: #f0c040;
    color:  black;
    font-weight: bold;
    font-size: 1.2em;
 }

h2 {
    background-color: #9B410E;
    color: white;
    text-align: left;
    font-weight: bold;
    padding: 2px;
    border-top: 1px solid black;
}

body {
    background: black;
}

p, table, div {
    background: #f0ead8;
}

p {
    margin: 0;
    font-style: italic;
    text-align: center;
}

table {
    width: 100%;
    border-collapse:collapse;
    border-color:#000000;
}

th, td {
    font-family: monospace;
    vertical-align: top;
    text-align: left;
}

td {
    color: green;
    word-break:break-all;
}

.odd {
    background-color: #eeeeee;
}

.even {
    background-color: #fafafa;
}

.exectime {
    font-size: 0.8em;
    font-style: italic;
}

#table_assigned_vars th {
    color: blue;
}

#table_config_vars th {
    color: maroon;
}
/* ]]> */
</style>
{/literal}
</head>
<body>

<h1>KISS Debug Console</h1>
EOF;
        foreach (self::$_info as $row) {
            if ($row[3] == 'Object' && $row[4] == 'Constructed') {
                if (isset($object[$row[2]])) {
                    $object[$row[2]] ++;
                } else {
                    $object[$row[2]] = 1;
                }
            }
        }
        arsort($object);
        $echo .= "<h2>实例创建次数</h2>";
        $echo .= "<table border=1><tr><th>Class</th><th width='60'>创建次数</th></tr>";
        foreach ($object as $key => $value) {
            if ($value > 1) {
                $echo .= "<tr><td>{$key}</td><td><font color=red><b>{$value}</b></font></td></tr>";
            } else {
                $echo .= "<tr><td>{$key}</td><td>{$value}</td></tr>";
            }
        }
        $echo .= "</table>";
        foreach (self::$_info as $row) {
            if ($row[3] == 'SQLQuery') {
                if (isset($sql[$row[4]])) {
                    $sql[$row[4]] ++;
                } else {
                    $sql[$row[4]] = 1;
                }
            }
        }
        if (count($sql) > 0) {
            arsort($sql);
        }
        $echo .= "<h2>SQL执行次数</h2>";
        $echo .= "<table border=1><tr><th>SQL</th><th width='60'>查询次数</th></tr>";
        foreach ($sql as $key => $value) {
            if ($value > 1) {
                $echo .= "<tr><td>{$key}</td><td><font color=red><b>{$value}</b></font></td></tr>\n";
            } else {
                $echo .= "<tr><td>{$key}</td><td>{$value}</td></tr>\n";
            }
        }
        $echo .= "</table>";
        $echo .= "<h2>实例创建顺序</h2>";
        $i     = 0;
        $echo .= "<table border=1><tr><th width='30'>序号</th><th>时间</th><th>标识ID</th><th>实例类型</th><th>信息类型</th><th>信息</th></tr>";
        foreach (self::$_info as $row) {
            if ($row[3] == 'Object') {
                $echo .= "<tr><td>" . ++ $i . "</td>";
                foreach ($row as $cell) {
                    $echo .= "<td>{$cell}</td>";
                }
                $echo .= "</tr>\n";
            }
        }
        $x     = 0;
        $echo .= "</table>";
        $echo .= "<h2>SQL执行顺序</h2>";
        $echo .= "<table border=1><tr><th width='40'>序号</th><th>SQL</th></tr>";
        foreach (self::$_info as $row) {
            if ($row[3] == 'SQLQuery') {
                $echo .= "<tr><td>" . ++ $x . "</td><td>{$row[4]}</td></tr>\n";
            }
        }
        $echo .= "</table></body></html>";
        return $echo;
    }
    public function dumpinfo ()
    {
        $content = self::smartyModifierEscape(self::getDumpInfo(), 'javascript');
        $time = microtime(true);
        echo <<<EOF
<script type="text/javascript">
// <![CDATA[
    _kiss_debug_console = window.open('','_blank',"width=680,height=600,resizable,scrollbars=yes");
    _kiss_debug_console.document.write('$content');
    _kiss_debug_console.document.close();
// ]]>
</script>
EOF;
    }
    public function setDebugInfo ($pInfo)
    {
        list($msec, $sec) = explode(" ", microtime());
        array_push(self::$_info, array_merge(array(
            date("m-d H:i:j") . substr($msec, 1)), $pInfo));
    }
    public function getDebugInfo ()
    {
        return self::$_info;
    }
    /**
     * 转义
     *
     * @param string $string   原始字串
     * @param string $esc_type 转移类型
     * @param string $char_set 字符集
     *
     * @return string
     */
    function smartyModifierEscape ($string, $esc_type = 'html', $char_set = 'ISO-8859-1')
    {
        switch ($esc_type) {
        case 'html':
            return htmlspecialchars($string, ENT_QUOTES, $char_set);
        case 'htmlall':
            return htmlentities($string, ENT_QUOTES, $char_set);
        case 'url':
            return rawurlencode($string);
        case 'urlpathinfo':
            return str_replace('%2F', '/', rawurlencode($string));
        case 'quotes':
            // escape unescaped single quotes
            return preg_replace("%(?<!\\\\)'%", "\\'", $string);
        case 'hex':
            // escape every character into hex
            $return = '';
            for ($x = 0; $x < strlen($string); $x ++) {
                $return .= '%' . bin2hex($string[$x]);
            }
            return $return;
        case 'hexentity':
            $return = '';
            for ($x = 0; $x < strlen($string); $x ++) {
                $return .= '&#x' . bin2hex($string[$x]) . ';';
            }
            return $return;
        case 'decentity':
            $return = '';
            for ($x = 0; $x < strlen($string); $x ++) {
                $return .= '&#' . ord($string[$x]) . ';';
            }
            return $return;
        case 'javascript':
            // escape quotes and backslashes, newlines, etc.
            return strtr($string, array(
                '\\' => '\\\\',
                "'" => "\\'",
                '"' => '\\"',
                "\r" => '\\r',
                "\n" => '\\n',
                '</' => '<\/'));
        case 'mail':
            // safe way to display e-mail address on a web page
            return str_replace(array(
                '@',
                '.'), array(
                ' [AT] ',
                ' [DOT] '), $string);
        case 'nonstd':
            // escape non-standard chars, such as ms document quotes
            $_res = '';
            for ($_i = 0, $_len = strlen($string); $_i < $_len; $_i ++) {
                $_ord = ord(substr($string, $_i, 1));
                // non-standard char, escape it
                if ($_ord >= 126) {
                    $_res .= '&#' . $_ord . ';';
                } else {
                    $_res .= substr($string, $_i, 1);
                }
            }
            return $_res;
        default:
            return $string;
        }
    }
}
?>