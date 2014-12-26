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
 * KISS_Util_String
 *
 * @category  Core
 * @package   KISS
 * @author    iwater <iwater@gmail.com>
 * @copyright 2003-2009 iwater
 * @license   http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version   Release: 3.5.0
 * @link      http://www.kissphp.cn
 */
class KISS_Util_String
{

    /**
     * 多字节版 substr
     *
     * @param string $string 原始字串
     * @param int    $length 截取长度
     * @param array  $option 增加一个 option 参数组，提供如下选项：
     * add_dot 是否需要在截取后添加 ...(会计入总长度)，默认为 true，添加；
     * charset 字符集 utf-8 or gb2312，默认为 utf-8；
     * char_len $length 是 ascii 长度还是字符长度，默认为 false，ascii长度
     *
     * @return string
     */
    public static function substr ($string, $length, $option = array())
    {
        $strcut    = '';
        $strLength = 0;
        $i_option  = array(
            'add_dot' => true,
            'charset' => 'utf-8',
            'char_len' => false);
        $option    = array_merge($i_option, $option);
        if (strlen($string) > $length) {
            //将$length换算成实际UTF8格式编码下字符串的长度
            for ($i = 0; $i < ($length - ($option['add_dot'] ? 3 : 0)); $i ++) {
                if ($strLength >= strlen($string)) {
                    break;
                }
                    //当检测到一个中文字符时
                if (ord($string[$strLength]) > 127) {
                    if ($option['char_len'] || ++ $i < ($length - ($option['add_dot'] ? 3 : 0))) {
                        $strLength += (($option['charset'] == 'utf-8') ? 3 : 2);
                    }
                } else {
                    $strLength += 1;
                }
            }
            return substr($string, 0, $strLength) . ($option['add_dot'] ? '...' : '');
        } else {
            return $string;
        }
    }
    /**
     * utf-8 版 strlen，不区分中英文，长度都为1
     *
     * @param string $str           原始字串
     * @param int    $chaneseLength 忘了
     *
     * @return int
     */
    public static function strlen ($str, $chaneseLength = 1)
    {
        $i     = 0;
        $count = 0;
        $len   = strlen($str);
        while ($i < $len) {
            $chr = ord($str[$i]);
            $count ++;
            $i ++;
            if ($i >= $len) {
                break;
            }
            if ($chr & 0x80) {
                $chr <<= 1;
                while ($chr & 0x80) {
                    $i ++;
                    $chr <<= $chaneseLength;
                }
            }
        }
        return $count;
    }
    /**
     * 把字符串拆分成哈希数组，默认参数下等效于PHP内置函数 parse_str
     *
     * @param string $string 原始字串
     * @param chat   $split  字段分割字符
     * @param char   $equ    键值分割字符
     *
     * @return array
     */
    public static function parseStr($string, $split='&', $equ='='){
        $array = explode($split, $string);
        $ret = array();
        foreach ($array as $item){
            $_temp = explode($equ, $item);
            $ret[$_temp[0]] = $_temp[1];
        }
        return $ret;
    }
}
?>