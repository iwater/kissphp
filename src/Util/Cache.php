<?php
/**
*
* @author 马涛 <matao@bj.tom.com>
* @version v 2.0 alpha 2003/12/03
* @package Core_Class
*/

/**
* Cache 的 SQL 实现
*/
class KISS_Util_Cache {
    function setSqlCache($pSql,$pTimeStamp,$pResult) {
        Cache::removeSqlCache($pSql);
        file_put_contents(Cache::getSqlCacheFileName($pSql,$pTimeStamp),serialize($pResult));
    }

    function haveSqlCache($pSql,$pTimeStamp) {
        return file_exists(Cache::getSqlCacheFileName($pSql,$pTimeStamp));
    }
    
    function getSqlCache($pSql,$pTimeStamp) {
        return unserialize(file_get_contents(Cache::getSqlCacheFileName($pSql,$pTimeStamp)));
    }
    
    function getSqlCacheFileName($pSql,$pTimeStamp) {
        return KISS_Framework_Config::getSystemPath('temp')."/cache/cache.".md5($pSql).".".md5($pTimeStamp).".serialize";
    }

    function removeSqlCache($pSql) {
        $files = glob(KISS_Framework_Config::getSystemPath('temp')."/cache/cache.".md5($pSql).".*.serialize");
        if(is_array($files) && count($files)>0) {
            foreach ($files as $filename) {
                unlink($filename);
            }
        }
    }
}
?>