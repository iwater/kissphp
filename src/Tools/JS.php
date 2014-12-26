<?php
class KISS_Tools_JS extends KISS_Page {
    private $new_class_found = false;
    private $load_array = array();
    private $js_cache_file;
    private $js_cache_file_content;
    function __construct() {}

    function run() {
        header("Content-type: text/html; charset=UTF-8");
        @$referer= strtolower($_SERVER['HTTP_REFERER']);
        $tmp = explode('?', $referer);
        $referer = $tmp[0];

        $cache_path = KISS_Framework_Config::getSystemPath("temp");
        if (file_exists($cache_path) && is_writable($cache_path)) {
            $this->js_cache_file = $cache_path.'/js.list.'.md5($referer);
            $this->js_cache_file_content = $cache_path.'/js.content.'.md5($referer).".js";
        }

        if (!is_null($this->js_cache_file)) {
            if(file_exists($this->js_cache_file)) {
                $this->load_array = unserialize(file_get_contents($this->js_cache_file));
            }

            $pack = self::getPackFileName($_GET["jspack"]);
            //$pack = $_GET["jspack"];
            if(in_array($pack, $this->load_array)) {
                echo file_get_contents($this->js_cache_file_content);
            } else {
                $this->load_array[] = $pack;
                $this->new_class_found = true;
                echo self::getFileContents($pack);
            }
        }
    }

    private static function getPackFileName($pName) {
        $array = explode("_", $pName);
        //array_pop($array);
        $package_name = array_pop($array);
        if(count($array)==0) {
            return $package_name;
        } else {
            return implode("/", $array)."/{$package_name}";
        }
    }

    private static function getFileContents($pPackName) {
        //CClass_Tools_Util::writeLog(time()."js.log", $pPackName."  load");
        $package_file_name = $pPackName.".js";

        $js_lib = array();
        $js_lib[] = dirname(KISS_Framework_Config::getRootPath())."/scripts";
        $js_lib[] = dirname(dirname(dirname(__FILE__))).'/NonPHP/kiss/libs';

        $config_dir =  KISS_Framework_Config::getParam("jslib");
        if($config_dir!="") {
            $tmp = explode(";", $config_dir);
            $js_lib = array_merge($js_lib, $tmp);
        }

        foreach($js_lib as $lib) {
            $file_name=$lib."/".$package_file_name;

            if(file_exists($file_name)) {
                //CClass_Tools_Util::writeLog("js.log", $file_name);
                $content = file_get_contents($file_name);

                $pattern = "#^\s*import\s+([a-zA-Z_0-9]+);\s*$#ms";
                if(preg_match_all($pattern, $content, $matches, PREG_SET_ORDER)) {
                    $result = print_r($matches, true);
                    //CClass_Tools_Util::writeLog("js.log", $result);
                    foreach($matches as $match) {
                        //CClass_Tools_Util::writeLog("{$match[1]}.log", self::getFileContents( self::getPackFileName($match[1]) ));
                        $content = str_replace($match[0], self::getFileContents( self::getPackFileName($match[1]) ), $content);
                        //CClass_Tools_Util::writeLog("tmp2.log", $match[0]);
                        //break;
                    }
                }
                $content=self::zipJs($content);

                //
                $content .= sprintf("\n_kiss_js._load_pack_push('%s');\n", str_replace('/','_',$pPackName));
                //    echo sprintf("\n_kiss_js._load_pack.push('%s');\n", str_replace('/','_',$pPackName));;

                //CClass_Tools_Util::writeLog("tmp444.log", $content);
                return self::str2utf8($content);
            }
        }

        //CClass_Tools_Util::writeLog("js.log", $pPackName." not founded!");
        return "";
    }

    private function debug_msg($s) {
        echo '/*'.$s.'*/';
    }

    public function __destruct() {
        if ($this->new_class_found) {
            file_put_contents($this->js_cache_file, serialize($this->load_array));

            $contents = array();
            foreach($this->load_array as $row) {
                $contents[] = KISS_Tools_JS::getFileContents($row);
            }
            file_put_contents($this->js_cache_file_content, implode("\n", $contents));
            //echo "alert();";
        }
    }

    private static function zipJs($content) {
        $content = preg_replace("#^\s*//.*?\n#msi", "\n", $content);

        $pattern = "#^\s*/\*.+?\*/#msi";
//        preg_match_all($pattern, $content, $matches);
////                //$result = print_r($matches, true);
//        CClass_Tools_Util::writeLog("l.log", print_r($matches, true));
        $content = preg_replace($pattern, '', $content);
//        //
//        $content = preg_replace("#^\s*return\s+([^\$_0-9a-zA-Z].*?)\s*;?\s*\n#msi", "return\$1\n", $content);
//        $content = preg_replace("#^\s*case\s+(.*?)\s*:\s*\n#msi", "case \$1:\n", $content);
//        //$content = preg_replace("#^\s*function\s+([\$_0-9a-zA-Z]+)\((.*?)\)\s*:\s*\n#msi", "case \$1:\n", $content);
//        $content = preg_replace("#^\s*(var\s)?\s*([\$_0-9a-zA-Z]+)\s+=\s+(.*?)\s*\n#msi", "\$1\$2=\$3\n", $content);
//
//        //
        $content = preg_replace("#^\s*(.*?)\s*\n#msi", "\$1\n", $content);
        $content = preg_replace("#\r#msi", "\n", $content);
        $content = preg_replace("#\n+#msi", "\n", $content);
        $content = preg_replace("#^\n+#msi", "", $content);
        $content = preg_replace("#\n+$#msi", "", $content);
//
////                $content = preg_replace("#\s*;*\s*\}\s*?$#msi", "}", $content);
////                $content = preg_replace("#\}\n([,;}])#msi", "}\$1", $content);
//        //$content = preg_replace("#\)\n#msi", ")", $content);
////                $content = preg_replace("#\s*\{\n+#msi", "{\n", $content);
//        $content = preg_replace("#\s*,\n+#msi", ",", $content);
//        $content = preg_replace("#\s*;\n+#msi", ";", $content);
//
//        //
//        $content = preg_replace("#;\}$#msi", "}", $content);

        return $content;
    }

    function str2utf8($s) {
        return iconv('GB18030','UTF-8',$s);
    }
}
?>