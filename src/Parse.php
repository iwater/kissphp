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
 * @category  KISS
 * @package   Core
 * @author    iwater <iwater@gmail.com>
 * @copyright 2003-2009 iwater
 * @license   http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version   SVN: $Id: User.php 109 2008-12-04 06:13:55Z matao $
 * @link      http://www.kissphp.cn
 */

/**
 * KISS_Parse
 *
 * @category  KISS
 * @package   Core
 * @author    iwater <iwater@gmail.com>
 * @copyright 2003-2009 iwater
 * @license   http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version   Release: 3.5.0
 * @link      http://www.kissphp.cn
 */
class KISS_Parse
{
    /**
     * 构造函数
     *
     * @param string $source_file php源文件
     */
    public function __construct ($source_file)
    {
        $tokens          = token_get_all(file_get_contents($source_file));
        $last_comment    = array();
        $class_offset    = 0;
        $bracket_offset  = 0;
        $function_offset = 0;
        $class           = false;
        $function        = false;
        $class_name      = '';
        foreach ($tokens as $token) {
            if (is_array($token)) {
                switch ($token[0]) {
                //注释
                case 366:
                case 365:
                    $comment = trim($token[1]);
                    if (substr($comment, 0, 3) == '//#') {
                        $temp                   = explode(':', substr($comment, 3));
                        $last_comment[$temp[0]] = (count($temp) == 2) ? $temp[1] : '';
                    }
                    break;
                // 变量
                case 309:
                    if ($class_offset == 1 && $bracket_offset == 0) {
                        if (count($last_comment) > 0) {
                            KISS_Framework_Config::$annotation['class_var'][$class_name][substr($token[1], 1)] = $last_comment;
                        }
                        $last_comment = array();
                    }
                    break;
                // class
                case 352:
                    $class = true;
                    break;
                case 333:
                    $function = true;
                    break;
                // string
                case 307:
                    if ($class) {
                        if (count($last_comment) > 0) {
                            KISS_Framework_Config::$annotation['class'][$token[1]] = $last_comment;
                        }
                        $last_comment = array();
                        $class_name   = $token[1];
                        $class        = false;
                    }
                    if ($function) {
                        if (count($last_comment) > 0) {
                            KISS_Framework_Config::$annotation['class_function'][$class_name][$token[1]] = $last_comment;
                        }
                        $last_comment = array();
                        $function     = false;
                    }
                    break;
                default:
                    break;
                }
            } else {
                switch ($token) {
                case '{':
                    $class_offset ++;
                    break;
                case '}':
                    $class_offset --;
                    break;
                case '(':
                    $bracket_offset ++;
                    break;
                case ')':
                    $bracket_offset --;
                    break;
                default:
                    break;
                }
            }
        }
    }
}
?>