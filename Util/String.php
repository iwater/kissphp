<?php
class KISS_Util_String {
  
  // 多字节版 substr
  // 增加一个 option 参数组，提供如下选项
  // add_dot 是否需要在截取后添加 ...(会计入总长度)，默认为 true，添加
  // charset 字符集 utf-8 or gb2312，默认为 utf-8
  // char_len $length 是 ascii 长度还是字符长度，默认为 false，ascii长度
  public static function substr($string, $length, $option = array()) {
    $strcut = '';
    $strLength = 0;
    $i_option = array('add_dot'=>true, 'charset'=>'utf-8', 'char_len'=>false);
    $option = array_merge($i_option, $option);
    if(strlen($string) > $length) {
      //将$length换算成实际UTF8格式编码下字符串的长度
      for($i = 0; $i < ($length-($option['add_dot']?3:0)); $i++) {
        if ( $strLength >= strlen($string) )
        break;
        //当检测到一个中文字符时
        if( ord($string[$strLength]) > 127 ) {
          if ($option['char_len'] || ++$i < ($length-($option['add_dot']?3:0))) {
            $strLength += (($option['charset'] == 'utf-8')?3:2);
          }
        }
        else
        $strLength += 1;
      }
      return substr($string, 0, $strLength).($option['add_dot']?'...':'');
    } else {
      return $string;
    }
  }

  // utf-8 版 strlen
  public static function strlen($str,$chaneseLength = 1) {
    $i = 0;
    $count = 0;
    $len = strlen ($str);
    while ($i < $len) {
      $chr = ord ($str[$i]);
      $count++;
      $i++;
      if ($i >= $len)
      break;

      if ($chr & 0x80) {
        $chr <<= 1;
        while ($chr & 0x80) {
          $i++;
          $chr <<= $chaneseLength;
        }
      }
    }
    return $count;
  }
}
?>