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
 * 分布式文件系统
 *
 * @category  Core
 * @package   KISS
 * @author    iwater <iwater@gmail.com>
 * @copyright 2003-2009 iwater
 * @license   http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version   Release: 3.5.0
 * @link      http://www.kissphp.cn
 */
class KISS_DistributedFileSystem
{
    private $_Root;
    public function __construct ()
    {
        $this->_Root = dirname($_SERVER['SCRIPT_FILENAME']);
    }
    public function delete ($pFilename)
    {
        $filename = $this->_Root . $pFilename;
        @unlink($filename);
        return ! file_exists($filename);
    }
    public function freeSpace ()
    {
        return disk_free_space($this->_Root) / 1024;
    }
    public function uploadByURL ($pUrl, $pCheckSize = 0, $pCheckMD5 = '')
    {
        $url_info        = parse_url($pUrl);
        $return          = pathinfo($url_info['path']);
        $path_relatively = date("/Y/m/d", time());
        $path_absolute   = "{$this->_Root}{$path_relatively}";
        do {
            $file          = uniqid();
            $file_absolute = "{$path_absolute}/{$file}";
        } while (file_exists($file_absolute));
        $this->_exec("mkdir -p {$path_absolute}");
        $this->_exec("wget -q -O '{$file_absolute}' '{$pUrl}'");
        if (file_exists($file_absolute) && ((0 == $pCheckSize) || (filesize($file_absolute) == $pCheckSize)) && (('' == $pCheckMD5) || (md5_file($file_absolute) == $pCheckMD5))) {
            $return['size']        = filesize($file_absolute);
            $return['system_path'] = $path_relatively;
            $return['system_file'] = $file;
            return $return;
        }
        @unlink($file_absolute);
        return array();
    }
    function startDownload ($pRealFile, $pCaption)
    {
        $pRealFile = $this->_Root . $pRealFile;
        if (! is_file($pRealFile)) {
            header("HTTP/1.0 404 Not Found");
        }
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0,pre-check=0");
        #header("Cache-Control:");
        #header("Cache-Control: public");
        header("Content-Description: File Transfer");
        header("Content-Type: " . $this->_getContentType($pCaption));
        $filespaces = str_replace("_", " ", $pCaption);
        header("Content-Disposition: attachment; filename={$filespaces}");
        header("Content-Transfer-Encoding: binary");
        $size = filesize($pRealFile);
        //check if http_range is sent by browser (or download manager)
        if (isset($_SERVER['HTTP_RANGE'])) {
            list($a, $range) = explode("=", $_SERVER['HTTP_RANGE']);
            //if yes, download missing part
            str_replace($range, "-", $range);
            $size2 = $size - 1;
            header("Content-Range: $range$size2/$size");
            $new_length = $size2 - $range;
            header("Content-Length: $new_length");
            //if not, download whole file
        } else {
            $size2 = $size - 1;
            header("Content-Range: bytes 0-$size2/$size");
            header("Content-Length: " . $size2);
        }
        //open the file
        $fp = @fopen($pRealFile, "rb");
        //seek to start of missing part
        fseek($fp, $range);
        //start buffered download
        while (! feof($fp)) {
            //reset time limit for big files
            set_time_limit();
            print(fread($fp, 1024 * 8));
            flush();
        }
        fclose($fp);
    }
    private function _exec ($pCommand)
    {
        return `$pCommand`;
    }
    private function _getContentType ($pFilename)
    {
        $FileType  = array(
            'cdf' => 'application/x-cdf',
            'fif' => 'application/fractals',
            'spl' => 'application/futuresplash',
            'hta' => 'application/hta',
            'hqx' => 'application/mac-binhex40',
            'doc' => 'application/msword',
            'pdf' => 'application/pdf',
            'p10' => 'application/pkcs10',
            'p7m' => 'application/pkcs7-mime',
            'p7s' => 'application/pkcs7-signature',
            'cer' => 'application/x-x509-ca-cert',
            'crl' => 'application/pkix-crl',
            'ps' => 'application/postscript',
            'setpay' => 'application/set-payment-initiation',
            'setreg' => 'application/set-registration-initiation',
            'smi' => 'application/smil',
            'edn' => 'application/vnd.adobe.edn',
            'pdx' => 'application/vnd.adobe.pdx',
            'rmf' => 'application/vnd.adobe.rmf',
            'xdp' => 'application/vnd.adobe.xdp+xml',
            'xfd' => 'application/vnd.adobe.xfd+xml',
            'xfdf' => 'application/vnd.adobe.xfdf',
            'fdf' => 'application/vnd.fdf',
            'xls' => 'application/x-msexcel',
            'sst' => 'application/vnd.ms-pki.certstore',
            'pko' => 'application/vnd.ms-pki.pko',
            'cat' => 'application/vnd.ms-pki.seccat',
            'stl' => 'application/vnd.ms-pki.stl',
            'ppt' => 'application/x-mspowerpoint',
            'wpl' => 'application/vnd.ms-wpl',
            'rms' => 'video/vnd.rn-realvideo-secure',
            'rm' => 'application/vnd.rn-realmedia',
            'rmvb' => 'application/vnd.rn-realmedia-vbr',
            'rnx' => 'application/vnd.rn-realplayer',
            'rjs' => 'application/vnd.rn-realsystem-rjs',
            'rjt' => 'application/vnd.rn-realsystem-rjt',
            'rmj' => 'application/vnd.rn-realsystem-rmj',
            'rmx' => 'application/vnd.rn-realsystem-rmx',
            'rmp' => 'application/vnd.rn-rn_music_package',
            'rsml' => 'application/vnd.rn-rsml',
            'z' => 'application/x-compress',
            'tgz' => 'application/x-compressed',
            'etd' => 'application/x-ebx',
            'gz' => 'application/x-gzip',
            'ins' => 'application/x-internet-signup',
            'iii' => 'application/x-iphone',
            'jnlp' => 'application/x-java-jnlp-file',
            'latex' => 'application/x-latex',
            'nix' => 'application/x-mix-transfer',
            'mxp' => 'application/x-mmxp',
            'asx' => 'video/x-ms-asf-plugin',
            'wmd' => 'application/x-ms-wmd',
            'wmz' => 'application/x-ms-wmz',
            'p12' => 'application/x-pkcs12',
            'p7b' => 'application/x-pkcs7-certificates',
            'p7r' => 'application/x-pkcs7-certreqresp',
            'swf' => 'application/x-shockwave-flash',
            'sit' => 'application/x-stuffit',
            'tar' => 'application/x-tar',
            'man' => 'application/x-troff-man',
            'zip' => 'application/x-zip-compressed',
            'xml' => 'text/xml',
            '3gp' => 'video/3gpp-encrypted',
            '3g2' => 'video/3gpp2',
            'aiff' => 'audio/x-aiff',
            'au' => 'audio/basic',
            'mid' => 'midi/mid',
            'mp3' => 'audio/x-mpg',
            'm3u' => 'audio/x-mpegurl',
            'ra' => 'audio/x-realaudio',
            'wav' => 'audio/x-wav',
            'wax' => 'audio/x-ms-wax',
            'wma' => 'audio/x-ms-wma',
            'ram' => 'audio/x-pn-realaudio',
            'bmp' => 'image/bmp',
            'gif' => 'image/gif',
            'jpg' => 'image/pjpeg',
            'png' => 'image/x-png',
            'tiff' => 'image/tiff',
            'rp' => 'image/vnd.rn-realpix',
            'ico' => 'image/x-icon',
            'xbm' => 'image/xbm',
            'css' => 'text/css',
            '323' => 'text/h323',
            'htm' => 'text/html',
            'uls' => 'text/iuls',
            'txt' => 'text/plain',
            'wsc' => 'text/scriptlet',
            'rt' => 'text/vnd.rn-realtext',
            'htt' => 'text/webviewhtml',
            'htc' => 'text/x-component',
            'iqy' => 'text/x-ms-iqy',
            'odc' => 'text/x-ms-odc',
            'rqy' => 'text/x-ms-rqy',
            'vcf' => 'text/x-vcard',
            'avi' => 'video/x-msvideo',
            'mpeg' => 'video/x-mpeg2a',
            'rv' => 'video/vnd.rn-realvideo',
            'wm' => 'video/x-ms-wm',
            'wmv' => 'video/x-ms-wmv',
            'wmx' => 'video/x-ms-wmx',
            'wvx' => 'video/x-ms-wvx');
        $pos       = strrpos($pFilename, ".");
        $extension = strtolower(substr($pFilename, $pos + 1));
        if (array_key_exists($extension, $FileType)) {
            return $FileType[$extension];
        } else {
            return "application/force-download";
        }
    }
}
?>