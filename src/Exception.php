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
 * KISS_Exception
 *
 * @category  Core
 * @package   KISS
 * @author    iwater <iwater@gmail.com>
 * @copyright 2003-2009 iwater
 * @license   http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version   Release: 3.5.0
 * @link      http://www.kissphp.cn
 */
class KISS_Exception extends Exception
{
    function __construct (string $message = null, int $code = 0)
    {
        if (func_num_args()) {
            $this->message = $message;
        }
        $this->code   = $code;
        $this->file   = __FILE__; // of throw clause
        $this->line   = __LINE__; // of throw clause
        $this->_trace = debug_backtrace();
        $this->string = self::_stringFormat($this);
    }
    protected $message = 'Unknown exception'; // exception message
    protected $code    = 0; // user defined exception code
    protected $file; // source filename of exception
    protected $line; // source line of exception
    private $_trace; // backtrace of exception
    private $_string; // internal only!!
    final function getMessage ()
    {
        return $this->message;
    }
    final function getCode ()
    {
        return $this->code;
    }
    final function getFile ()
    {
        return $this->file;
    }
    final function getTrace ()
    {
        return $this->_trace;
    }
    final function getTraceAsString ()
    {
        return self::_traceFormat($this);
    }
    function _toString ()
    {
        return $this->_string;
    }
    private static function _stringFormat (Exception $exception)
    {
        /* ... a function not available in PHP scripts
        that returns all relevant information as a string*/
    }
    private static function _traceFormat (Exception $exception)
    {
        /* ... a function not available in PHP scripts
        that returns the backtrace as a string*/
    }
}
?>