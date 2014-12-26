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
 * Util 常用函数类库
 *
 * @category  Core
 * @package   KISS
 * @author    iwater <iwater@gmail.com>
 * @copyright 2003-2009 iwater
 * @license   http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version   Release: 3.5.0
 * @link      http://www.kissphp.cn
 */
class KISS_Util_Util
{
    public static function pagination ($pPageNo, $pPageSize, $pResultCount ,$pageHash)
    {
        $pageHash                  = array (
        'mPage' => $pPageNo,
        'mPageSize' => $pPageSize,
        'mPageCount' => 1,
        'mNextPage' => 1,
        'mPreviousPage' => 1,
        'mFirstPage' => 1,
        'mLastPage' => 1,
        'mRecordCount' => $pResultCount,
        'mStartRecord' => 1,
        'mEndRecord' => $pResultCount,
        );
        $pageHash['mPageCount']    = ceil($pageHash['mRecordCount'] / $pageHash['mPageSize']);
        $pageHash['mLastPage']     = $pageHash['mPageCount'];
        $pageHash['mStartRecord']  = ($pageHash['mPage'] - 1) * $pageHash['mPageSize']+1;
        $pageHash['mEndRecord']    = min($pageHash['mRecordCount'], $pageHash['mPage'] * $pageHash['mPageSize']);
        $pageHash['mNextPage']     = min($pageHash['mPageCount'], $pageHash['mPage']+1);
        $pageHash['mPreviousPage'] = max(1, $pageHash['mPage']-1);
    }

    /*static function sendSms($pMobileNumber, $pContent)
    {
        $time     = time();
        $site_id  = "tom_free";
        $key      = "free_tom#";
        $sign     = md5($pMobileNumber.$time.$site_id.$key.$pContent);

        //inter_mt.php?mobile_no=13910088000&msg=%C4%E3&send_time=1018514142&site_ID= HN01&sign=32d499a525c0d57d80d8bf015c97daa8
        $template = "http://61.135.159.20/inter_mt.php?mobile_no=%s&msg=%s&send_time=%s&site_id=%s&sign=%s";
        $send_url = sprintf($template, $pMobileNumber, urlencode($pContent), $time, $site_id, $sign);
        //成功返回SUCC
        $result   = file_get_contents($send_url);
        return $pMobileNumber.":".$result;
    }*/

    public static function addSqlSlashes ($pString)
    {
        return str_replace("'", "\'", $pString);
    }

    public static function go2info($code, $back_url = "/")
    {
        self::directGoToUrl("Prompt.php?code={$code}&back_url=".rawurlencode($back_url));
        exit();
    }

    public static function directGoToUrl($pUrl = '/')
    {
        //var_dump(debug_backtrace());
        echo "<meta http-equiv='refresh' content='0;URL={$pUrl}'>";
        exit();
    }

    public static function setSerializeObject($pConfigName,$pObject)
    {
        File::writeFile(Util::getSerializeObjectPath($pConfigName), serialize($pObject));
    }

    public static function getSerializeObject($pConfigName)
    {
        File::writeFile($config_filename, serialize($this->mTableColumnHash));
    }

    public static function removeSerializeObject($pConfigName)
    {
    }

    public static function getSerializeObjectPath($pConfigName)
    {
        $path = "/www/webroot/digital/temp/";
        return $path.MD5($pConfigName)."serialize";
    }

    public static function transformarray2Hash ($pArray, $pPosKey = 0, $pPosValue = 1)
    {
        $return = Array();
        for ($i = 0;$i < count($pArray);$i++) {
            $return[$pArray[$i][$pPosKey]] = $pArray[$i][$pPosValue];
        }
        return $return;
    }

    public static function magicName ($pString, $pSeparation='')
    {
        return implode($pSeparation, array_map('ucfirst', explode('_', $pString)));
    }

    public static function chop ($pString)
    {
        return substr($pString, 0, strlen($pString)-1);
    }

    public static function strlenUTF8 ($str)
    {
        $i     = 0;
        $count = 0;
        $len   = strlen($str);
        while ($i < $len) {
            $chr = ord($str[$i]);
            $count++;
            $i++;
            if ($i >= $len) {
                break;
            }

            if ($chr & 0x80) {
                $chr <<= 1;
                while ($chr & 0x80) {
                    $i++;
                    $chr <<= 1;
                }
            }
        }
        return $count;
    }

    public static function substr_utf8($pTitle, $pLength, $sign = false)
    {
        if (strlen($pTitle) <= $pLength) {
            return $pTitle;
        }
        $tmpstr = "";
        if ($sign) {
            for ($i = 0;$i < $pLength;$i++) {
                if (ord(substr($pTitle, $i, 1)) > 0xa0) {
                    $tmpstr .= substr($pTitle, $i, 2);
                    $i++;
                } else {
                    $tmpstr .= substr($pTitle, $i, 1);
                }
            }
            return $tmpstr;
        }
        for ($i = 0;$i < $pLength-4;$i++) {
            if (ord(substr($pTitle, $i, 1)) > 0xa0) {
                $tmpstr .= substr($pTitle, $i, 2);
                $i++;
            } else {
                $tmpstr .= substr($pTitle, $i, 1);
            }
        }
        return $tmpstr . "...";
    }

    public static function subStrDoubleBytes($pTitle, $pLength, $sign = false)
    {
        if (strlen($pTitle) <= $pLength) {
            return $pTitle;
        }
        $tmpstr = "";
        if ($sign) {
            for ($i = 0;$i < $pLength;$i++) {
                if (ord(substr($pTitle, $i, 1)) > 0xa0) {
                    $tmpstr .= substr($pTitle, $i, 2);
                    $i++;
                } else {
                    $tmpstr .= substr($pTitle, $i, 1);
                }
            }
            return $tmpstr;
        }
        for ($i = 0;$i < $pLength-4;$i++) {
            if (ord(substr($pTitle, $i, 1)) > 0xa0) {
                $tmpstr .= substr($pTitle, $i, 2);
                $i++;
            } else {
                $tmpstr .= substr($pTitle, $i, 1);
            }
        }
        return $tmpstr . "...";
    }

    public static function addSpaceStrDoubleBytes($pString)
    {
        $tmpstr = "";
        $sign   = 1;
        for ($i = 0;$i < strlen($pString);$i++) {
            if (ord(substr($pString, $i, 1)) > 0xa0) {
                if ($sign == 0) {
                    $tmpstr .= " ";
                }
                $tmpstr .= substr($pString, $i, 2) . " ";
                $sign    = 1;
                $i++;
            } else {
                $tmpstr .= substr($pString, $i, 1);
                $sign    = 0;
            }
        }
        return $tmpstr;
    }

    public static function arrayChunk($pInputArray, $pSize, $pPreserveKeys)
    {
        $row  = 0;
        $cell = 0;
        if (is_array($pInputArray)) {
            foreach ($pInputArray as $temp) {
                $return[$row][$cell] = $temp;
                $cell++;
                if ($cell == $pSize) {
                    $row++;
                    $cell = 0;
                }
            }
        }
        return $return;
    }

    /*
    * 返回浮点型毫秒数
    */
    public static function getmicrotime()
    {
        list($usec, $sec) = explode(" ", microtime());
        return ((float)$usec + (float)$sec);
    }

    /* require PHP5
    */
    public static function autoLoadFiles($autoLoadPath)
    {
        $dir = new DirectoryIterator($autoLoadPath);
        foreach ($dir as $file) {
            if (! $file->isDir()) {
                include_once $file->getPathname();
            }
        }

    }

    public static function array2Hash ($pArray, $pOffset = 0)
    {
        $return = Array();
        for ($i=0;$i<count($pArray);$i++) {
            if (count($pArray[$i])==2) {
                $keys                                 = array_keys($pArray[$i]);
                $return[$pArray[$i][$keys[$pOffset]]] = $pArray[$i][$keys[1]];
            } else {
                $return[$pArray[$i][$pOffset]] = $pArray[$i];
            }
        }
        return $return;
    }

    public static function objectarray2Hash ($pArray, $pOffset = 0)
    {
        $return = Array();
        for ($i=0;$i<count($pArray);$i++) {
            $return[$pArray[$i]->$pOffset] = $pArray[$i];
        }
        return $return;
    }

    public static function array2Array ($pArray)
    {
        $return = Array();
        for ($i=0; $i<count($pArray); $i++) {
            $keys = array_keys($pArray[$i]);
            for ($j=0; $j<count($pArray[$i]); $j++) {
                $return[$keys[$j]][$i] = $pArray[$i][$keys[$j]];
            }
        }
        return $return;
    }

    public static function array2String ($pArray, $pOffset = 0)
    {
        if (is_array($pArray) && count($pArray) > 0) {
            $return = self::array2Array($pArray);
            if (array_key_exists($pOffset, $return)) {
                return "'".implode("','", array_unique($return[$pOffset]))."'";
            } else {
                $keys = array_keys($return);
                return "'".implode("','", array_unique($return[$keys[0]]))."'";
            }
        } elseif (is_object($pArray) && get_class($pArray) == 'KISS_KDO_SqlCommandIterator' && count($pArray) > 0) {
            foreach ($pArray as $item) {
                $return[] = $item[$pOffset];
            }
            return "'".implode("','", array_unique($return))."'";
        } else {
            return "";
        }
    }

    public static function countFileSize($Size)
    {
        return self::byteFormat($Size);
    }

    public static function byteFormat($input, $dec=0)
    {
        $prefix_arr = array("B", "K", "M", "G", "T");
        $value      = round($input, $dec);
        $i          = 0;
        while ($value>1024) {
            $value /= 1024;
            $i++;
        }
        $return_str = round($value, $dec).$prefix_arr[$i];
        return $return_str;
    }

    public static function numberFormat($input, $dec=0)
    {
        $input = ereg_replace("[^0-9\.]", "", $input);
        return $return_str;
    }

    public static function generateStaticJavaScriptConfigFile($return, $pGlobalVarName, $pPath)
    {
        $content = "";
        for ($i=0; $i<count($return); $i++) {
            $key      = "";
            $contents = "";
            foreach ($return[$i] as $option_name => $option_value) {
                if ($key == "") {
                    $key = $pGlobalVarName."_".$option_value;
                }
                $contents .= "'{$option_value}',";
            }
            $content .= "var {$key} = new {$pGlobalVarName}({$contents})\r\n";
        }
        $sql = "update static_file set modify_time = ".time()." where static_file_name = 'static.{$pGlobalVarName}.js'";
        MySQL::noneResultQuery($sql);
        return File::writeFile($pPath."static.{$pGlobalVarName}.js", $content);
    }

    public static function generateStaticPHPConfigFile($return, $pGlobalVarName)
    {
        $content = "<?php\r\n";
        for ($i=0; $i<count($return); $i++) {
            $key = "";
            foreach ($return[$i] as $option_name => $option_value) {
                if ($key == "") {
                    $key = $pGlobalVarName."_".$option_value;
                }
                $content .= "\t\$global_vars[{$pGlobalVarName}][{$key}][".$option_name.'] = \''.$option_value.'\';'."\r\n";
            }
        }
        $content .= "?>\r\n";
        $sql      = "update static_file set modify_time = ".time()." where static_file_name = 'static.{$pGlobalVarName}.ini'";
        MySQL::noneResultQuery($sql);
        return File::writeFile(ROOT_PATH."static/static.{$pGlobalVarName}.ini", $content);
    }

    public static function generateStaticXMLConfigFile($return, $pGlobalVarName)
    {
        $content = "<?xml version=\"1.0\" encoding=\"GB2312\"?>\r\n<?xml-stylesheet type=\"text/xsl\" href=\"../scripts/catalog_list.xsl\"?>\r\n<{$pGlobalVarName}s>";
        for ($i=0;$i<count($return);$i++) {
            $content .= "<{$pGlobalVarName}>\r\n";
            $key      = "";
            foreach ($return[$i] as $option_name => $option_value) {
                $content .= "<$option_name>";
                $content .= "$option_value";
                $content .= "</$option_name>";
            }
            $content .= "</{$pGlobalVarName}>\r\n";
        }
        $content .= "</{$pGlobalVarName}s>\r\n";
        $sql      = "update static_file set modify_time = ".time()." where static_file_name = 'static.{$pGlobalVarName}.xml'";
        MySQL::noneResultQuery($sql);
        return File::writeFile(ROOT_PATH."static/static.{$pGlobalVarName}.xml", $content);
    }

    public static function html2js($pHtmlCode)
    {
        $return  = 'document.write("';
        $return .= str_replace("\r\n", '\r\n', addslashes($pHtmlCode));
        $return .= '");';
        return $return;
    }

    public static function randomWeighted($pArray, $pOffset = null)
    {
        $sum = 0;
        if (!is_null($pOffset)) {
            foreach ($pArray as $key => $value) {
                $innerArray[$key] = $value[$pOffset];
            }
        }
        $rand = rand(0, array_sum($innerArray)-1);
        foreach ($innerArray as $key => $value) {
            if ($rand >= $value) {
                $rand -= $value;
            } else {
                return $key;
            }
        }
    }

    public static function isMobileNumber ($mobile)
    {
        return (1 === preg_match('/^1[3|5|8][0-9]{9}$/', $mobile));
    }

    /**
    * 将一个表的一个或多个字段通过外键引用填充到另一个表的结果数据中
    *
    * @param array  &$messages     需要填充的数组
    * @param object &$pObject      外键关联到的实例
    * @param mixed  $pProperty     需填充的字段名，如果只填充一个字段可以是一个string，填充多个字段，则是一个array，需改变填充后的字段名，则传递一个hash
    * @param string $pJoinColumn   外键字段
    * @param string $pJoinedColumn 外键关联到的字段
    *
    * @return void
    */
    public static function fills(&$messages, &$pObject, $pProperty, $pJoinColumn, $pJoinedColumn)
    {
        if (count($messages) > 0) {
            $user_list = self::array2String($messages, $pJoinColumn);
            if (empty($pObject->mAdditionalCondition)) {
                $pObject->mAdditionalCondition = "{$pJoinedColumn} in ({$user_list})";
            } else {
                $pObject->mAdditionalCondition .= " and {$pJoinedColumn} in ({$user_list})";
            }
            $users = self::array2Hash($pObject->_list(), $pJoinedColumn);
            if (count($users) > 0) {
                foreach ($messages as &$item) {
                    if (is_array($pProperty)) {
                        foreach ($pProperty as $key => $value) {
                            if (isset($users[$item[$pJoinColumn]][$value])) {
                                if (is_string($key)) {
                                    $item[$key] = $users[$item[$pJoinColumn]][$value];
                                } else {
                                    $item[$value] = $users[$item[$pJoinColumn]][$value];
                                }
                            }
                        }
                    } else {
                        $item[$pProperty] = $users[$item[$pJoinColumn]][$pProperty];
                    }
                }
            }
        }
    }
}
?>