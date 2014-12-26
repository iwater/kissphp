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
 * 图形类库
 *
 * @category  Core
 * @package   KISS
 * @author    iwater <iwater@gmail.com>
 * @copyright 2003-2009 iwater
 * @license   http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version   Release: 3.5.0
 * @link      http://www.kissphp.cn
 */
class KISS_Util_Chart
{
    private $_mChartTitle;
    private $_mDataTitles;
    private $_mDataValues;
    private $_mCategories;
    private $_mServerAddressIP = "203.212.6.137";
    private $_mDateColumnName  = 'date';
    private $_mPicWidth        = 540;
    private $_mPicHeight       = 330;

    /**
     * 设置图片标题
     *
     * @param string $pChartTitle 标题
     *
     * @return void
     */
    public function setChartTitle ($pChartTitle)
    {
        $this->_mChartTitle = $this->_gb2312ToUNICODE($pChartTitle);
    }
    /**
     * 设置数据列名
     *
     * @param array $pDateColumnName 列名数组
     *
     * @return void
     */
    public function setDateColumnName ($pDateColumnName)
    {
        $this->_mDateColumnName = $pDateColumnName;
    }
    /**
     * 设置数据标题
     *
     * @param array $pDataTitles 标题数组
     *
     * @return void
     */
    public function setDataTitles ($pDataTitles)
    {
        $this->_mDataTitles = $pDataTitles;
    }
    /**
     * 设置数据值
     *
     * @param array $pDataValues 数据
     *
     * @return void
     */
    public function setDataValues ($pDataValues)
    {
        $this->_mDataValues = $pDataValues;
    }
    /**
     * 设置分类
     *
     * @param array $pCategories 分类
     *
     * @return void
     */
    public function setCategories ($pCategories)
    {
        $this->_mCategories = $pCategories;
    }
    /**
     * 设置图片宽高
     *
     * @param int $pWidth  宽
     * @param int $pHeight 高
     *
     * @return void
     */
    public function serImageSize ($pWidth, $pHeight)
    {
        $this->_mPicWidth  = $pWidth;
        $this->_mPicHeight = $pHeight;
    }
    /**
     * 生成曲线图
     *
     * @param string $pChartTitle 标题
     *
     * @return string
     */
    public function drawTimeLine ($pChartTitle)
    {
        $scripts = '';
        foreach ($this->_mDataTitles as $key => $value) {
            $listdata = '';
            foreach ($this->_mDataValues as $item) {
                $listdata .= "{$item[$this->_mDateColumnName]},{$item[$key]};";
            }
            if (! empty($value)) {
                $value = $this->_gb2312ToUNICODE($value);
            }
            $scripts .= "graph.setseries({$value};{$listdata})";
        }
        return $this->corda($scripts, "Chart9.pcxml", $pChartTitle);
    }
    /**
     * 生成柱状图
     *
     * @param string $pChartTitle 标题
     *
     * @return string
     */
    public function drawBar ($pChartTitle)
    {
        foreach ($this->_mDataValues as $item) {
            $listdata .= "{$item[$this->_mDateColumnName]};";
        }
        $scripts .= "graph.SetCategories({$listdata})";
        foreach ($this->_mDataTitles as $key => $value) {
            unset($listdata);
            foreach ($this->_mDataValues as $item) {
                $listdata .= "{$item[$key]};";
            }
            if (! empty($value)) {
                $value = $this->_gb2312ToUNICODE($value);
            }
            $scripts .= "graph.SetSeries({$value};{$listdata})";
        }
        return $this->corda($scripts, "Chart4.pcxml", $pChartTitle);
    }
    /**
     * 生成饼图
     *
     * @param string $pChartTitle 标题
     *
     * @return string
     */
    public function drawPie ($pChartTitle)
    {
        if (count($this->_mCategories) > 0) {
            foreach ($this->_mCategories as $val) {
                $val       = $this->_gb2312ToUNICODE($val);
                $listdata .= "{$val};";
            }
        }
        $scripts .= "graph.SetCategories({$listdata})";
        foreach ($this->_mDataTitles as $key => $value) {
            unset($listdata);
            foreach ($this->_mDataValues as $item) {
                $listdata .= "{$item[$key]};";
            }
            if (! empty($value)) {
                $value = $this->_gb2312ToUNICODE($value);
            }
            $scripts .= "graph.SetSeries({$value};{$listdata})";
        }
        return $this->corda($scripts, "Chart3.pcxml", $pChartTitle);
    }

    /**
     * 生成StackedBar
     *
     * @param string $pChartTitle 标题
     *
     * @return string
     */
    public function drawStackedBar ($pChartTitle)
    {
        if (count($this->_mCategories) > 0) {
            foreach ($this->_mCategories as $val) {
                $val       = $this->_gb2312ToUNICODE($val);
                $listdata .= "{$val};";
            }
        }
        $scripts .= "graph.SetCategories({$listdata})";
        foreach ($this->_mDataTitles as $key => $value) {
            unset($listdata);
            foreach ($this->_mDataValues as $item) {
                $listdata .= "{$item[$key]};";
            }
            if (! empty($value)) {
                $value = $this->_gb2312ToUNICODE($value);
            }
            $scripts .= "graph.SetSeries({$value};{$listdata})";
        }
        return $this->corda($scripts, "StackedBar.pcxml", $pChartTitle);
    }

    /**
     * 生成图表显示代码
     *
     * @param string $pcScript    脚本
     * @param string $template    模板
     * @param string $pChartTitle 标题
     *
     * @return string
     */
    function corda ($pcScript, $template, $pChartTitle)
    {
        $this->setChartTitle($pChartTitle);
        $myImage                          = new CordaEmbedder();
        $myImage->externalServerAddress   = "http://{$this->_mServerAddressIP}:2001";
        $myImage->internalCommPortAddress = "http://{$this->_mServerAddressIP}:2002";
        $myImage->appearanceFile          = "apfiles/" . $template;
        $myImage->userAgent               = $_SERVER['HTTP_USER_AGENT'];
        $myImage->width                   = $this->_mPicWidth;
        $myImage->height                  = $this->_mPicHeight;
        $myImage->language                = "EN";
        $myImage->pcScript                = "title.setText({$this->_mChartTitle}){$pcScript}";
        $myImage->outputType              = "JPEG";
        $myImage->imageType               = "JPEG";
        return $myImage->getEmbeddingHTML();
    }

    /**
     * GB2312转UNICODE
     *
     * @param string $pString 原始字串
     *
     * @return string
     */
    private function _gb2312ToUNICODE ($pString)
    {
        if (! empty($pString)) {
            $chs    = new Chinese("GB2312", "UNICODE", $pString);
            $string = $chs->ConvertIT();
            return preg_replace("/&#x([0-9A-F]{4});/", "%u\$1", $string);
        }
        return "";
    }
}
?>