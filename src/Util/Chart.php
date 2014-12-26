<?
/**
* @author ���� <matao@bj.tom.com>
* @version v 1.5 2003/12/03
* @package Core_Class
*/

/**
* ͼ�����
*/

class KISS_Util_Chart {
    var $mChartTitle;
    var $mDataTitles;
    var $mDataValues;
    var $mCategories;
    var $mServerAddressIP = "203.212.6.137";
    var $mDateColumnName = 'date';

    var $mPicWidth  = 540;
    var $mPicHeight = 330;

    var $debug = false;

    /**
    * ���캯��
    * @access public
    */
    function __construct() {
    }

    /**
    * ����ͼƬ����
    * @access public
    */
    function setChartTitle($pChartTitle) {
        $this->mChartTitle = $this->GB2312toUNICODE($pChartTitle);
    }

    /**
    * ������������
    * @access public
    */
    function setDateColumnName($pDateColumnName) {
        $this->mDateColumnName = $pDateColumnName;
    }

    /**
    * �������ݱ���
    * @access public
    */
    function setDataTitles($pDataTitles) {
        $this->mDataTitles = $pDataTitles;
    }

    /**
    * ��������ֵ
    * @access public
    */
    function setDataValues($pDataValues) {
        $this->mDataValues = $pDataValues;
    }

    /**
    * ���÷���
    * @access public
    */
    function setCategories($pCategories) {
        $this->mCategories = $pCategories;
    }
    
    public function serImageSize($pWidth, $pHeight){
        $this->mPicWidth = $pWidth;
        $this->mPicHeight = $pHeight;
    }

    /**
    * ��ʱ������ͼ��֧����Сʱ��ƬΪ��
    * @access public
    * @example draw.php ��ʱ������ͼ����
    */
    function drawTimeLine($pChartTitle) {
        $scripts = '';
        foreach($this->mDataTitles as $key => $value) {
            $listdata = '';
            foreach($this->mDataValues as $item) {
                $listdata .= "{$item[$this->mDateColumnName]},{$item[$key]};";
            }
            if(!empty($value)) {
                $value = $this->GB2312toUNICODE($value);
            }
            $scripts .= "graph.setseries({$value};{$listdata})";
        }
        return $this->corda($scripts, "Chart9.pcxml", $pChartTitle);
    }

    /**
    * ����״ͼ
    * @access public
    */
    function drawBar($pChartTitle) {
        foreach($this->mDataValues as $item) {
            $listdata .= "{$item[$this->mDateColumnName]};";
        }
        $scripts .= "graph.SetCategories({$listdata})";
        foreach( $this->mDataTitles as $key => $value )    {
            unset($listdata);
            foreach ($this->mDataValues as $item) {
                $listdata .= "{$item[$key]};";
            }
            if(!empty($value)) {
                $value = $this->GB2312toUNICODE($value);
            }
            $scripts .= "graph.SetSeries({$value};{$listdata})";
        }
        return $this->corda($scripts, "Chart4.pcxml", $pChartTitle);
    }

    /**
    * ����ͼ
    * @access public
    */
    function drawPie($pChartTitle) {
        if (count($this->mCategories) > 0)
        foreach( $this->mCategories as $val ) {
            $val = $this->GB2312toUNICODE($val);
            $listdata .= "{$val};";
        }
        $scripts .= "graph.SetCategories({$listdata})";
        foreach( $this->mDataTitles as $key => $value ) {
            unset($listdata);
            foreach ($this->mDataValues as $item) {
                $listdata .= "{$item[$key]};";
            }
            if(!empty($value)) {
                $value = $this->GB2312toUNICODE($value);
            }
            $scripts .= "graph.SetSeries({$value};{$listdata})";
        }
        return $this->corda($scripts, "Chart3.pcxml", $pChartTitle);
    }

    /**
    * ����״ͼ
    * graph.setcategories(Group 1; Group 2; Group 3)
    * graph.setseries(Item 1;54;75;85)
    * graph.setseries(Item 2;92;60;70)
    * graph.setseries(Item 3;68;87;37)
    * @access public
    */
    function drawStackedBar($pChartTitle) {
        if (count($this->mCategories) > 0)
        foreach( $this->mCategories as $val ) {
            $val = $this->GB2312toUNICODE($val);
            $listdata .= "{$val};";
        }
        $scripts .= "graph.SetCategories({$listdata})";
        foreach( $this->mDataTitles as $key => $value ) {
            unset($listdata);
            foreach ($this->mDataValues as $item) {
                $listdata .= "{$item[$key]};";
            }
            if(!empty($value)) {
                $value = $this->GB2312toUNICODE($value);
            }
            $scripts .= "graph.SetSeries({$value};{$listdata})";
        }
        return $this->corda($scripts, "StackedBar.pcxml", $pChartTitle);
    }

    /**
    *
    * @access private
    */
    function corda( $pcScript, $template ,$pChartTitle ) {
        $this->setChartTitle($pChartTitle);
        $myImage = new CordaEmbedder();
        $myImage->externalServerAddress = "http://{$this->mServerAddressIP}:2001";
        $myImage->internalCommPortAddress = "http://{$this->mServerAddressIP}:2002";
        $myImage->appearanceFile = "apfiles/".$template;
        $myImage->userAgent = $_SERVER['HTTP_USER_AGENT'];
        $myImage->width = $this->mPicWidth;
        $myImage->height = $this->mPicHeight;
        $myImage->language = "EN";
        $myImage->pcScript = "title.setText({$this->mChartTitle}){$pcScript}";
        $myImage->outputType = "JPEG";
        $myImage->imageType = "JPEG";
        return $myImage->getEmbeddingHTML();
    }

    /**
    *
    * @access private
    */
    function GB2312toUNICODE($pString) {
        if(!empty($pString)) {
            $chs = new Chinese("GB2312","UNICODE",$pString);
            $string = $chs->ConvertIT();
            return preg_replace("/&#x([0-9A-F]{4});/", "%u\$1", $string);
        }
        return "";
    }
}
?>