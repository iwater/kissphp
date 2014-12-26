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
 * Excel 报表生成类
 *
 * @category  Core
 * @package   KISS
 * @author    iwater <iwater@gmail.com>
 * @copyright 2003-2009 iwater
 * @license   http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version   Release: 3.5.0
 * @link      http://www.kissphp.cn
 */
class KISS_Util_Excel
{
    private $_mDataTitles;
    private $_mDataValues;
    /**
     * 设置数据头
     *
     * @param array $pDataTitles 数据头
     *
     * @return void
     */
    public function setDataTitles ($pDataTitles)
    {
        $this->_mDataTitles = $pDataTitles;
    }
    /**
     * 设置数据
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
     * 快速导出
     *
     * @return void
     */
    public function simpleExport ()
    {
        $workbook = new Spreadsheet_Excel_Writer();
        $workbook->send('test.xls');
        for ($i = 0; $i < count($this->_mDataValues); $i ++) {
            $worksheets = &$workbook->addWorksheet();
            for ($j = 0; $j < count($this->_mDataValues[$i]); $j ++) {
                for ($k = 0; $k < count($this->_mDataValues[$i][$j]); $k ++) {
                    $worksheets->write(0, $k, $this->_mDataTitles[$i][$k]);
                    $worksheets->write($j + 1, $k, $this->_mDataValues[$i][$j][$k]);
                }
            }
        }
        $workbook->close();
    }
    /**
     * 生成报表
     *
     * @param bool $pShowHeader 是否添加字段表头
     *
     * @return void
     */
    public function keyExport ($pShowHeader = false)
    {
        foreach ($this->_mDataValues as $workbook_name => $worksheet_array) {
            $workbook = new Spreadsheet_Excel_Writer();
            $workbook->send("{$workbook_name}.xls");
            if ($pShowHeader) {
                $format_header = &$workbook->addFormat();
                $format_header->setSize(12);
                $format_header->setFontFamily('宋体');
                $format_header->setFgColor('yellow');
                $format_header->setBorder(1);
                $format_header->setAlign('center');
                $format_text = &$workbook->addFormat();
                $format_text->setSize(12);
                $format_text->setFontFamily('宋体');
                $format_numeric = &$workbook->addFormat();
                $format_numeric->setSize(12);
                $format_numeric->setFontFamily('宋体');
                $format_numeric->setNumFormat('#,##0.00');
                $format_numeric0 = &$workbook->addFormat();
                $format_numeric0->setSize(12);
                $format_numeric0->setFontFamily('宋体');
                $format_numeric0->setNumFormat('#,##0');
                $format_numeric1 = &$workbook->addFormat();
                $format_numeric1->setSize(12);
                $format_numeric1->setFontFamily('宋体');
                $format_numeric1->setNumFormat('#,##0.0');
                $format['text']     = &$format_text;
                $format['numeric']  = &$format_numeric;
                $format['numeric0'] = &$format_numeric0;
                $format['numeric1'] = &$format_numeric1;
            }
            foreach ($worksheet_array as $worksheet_name => $row_array) {
                $worksheets = &$workbook->addWorksheet($worksheet_name);
                $row        = 0;
                foreach ($row_array as $column_array) {
                    if (is_array($this->_mDataTitles[$worksheet_name][0])) {
                        if ($pShowHeader && $row == 0) {
                            for ($column = 0; $column < count($this->_mDataTitles[$worksheet_name]); $column ++) {
                                $worksheets->write(0, $column, $this->_mDataTitles[$worksheet_name][$column]['title'], $format_header);
                            }
                            $row ++;
                        }
                        for ($column = 0; $column < count($this->_mDataTitles[$worksheet_name]); $column ++) {
                            $worksheets->write($row, $column, $column_array[$this->_mDataTitles[$worksheet_name][$column]['key']], $format[$this->_mDataTitles[$worksheet_name][$column]['type']]);
                        }
                    } else {
                        $column = 0;
                        if ($pShowHeader && $row == 0) {
                            $row ++;
                        }
                        foreach ($column_array as $key => $value) {
                            if ($pShowHeader && $row == 1) {
                                if (isset($this->_mDataTitles)) {
                                    $worksheets->write(0, $column, $this->_mDataTitles[$worksheet_name][$key], $format_header);
                                } else {
                                    $worksheets->write(0, $column, $key, $format_header);
                                }
                            }
                            if (is_numeric($value)) {
                                $worksheets->write($row, $column, $value, $format_numeric);
                            } else {
                                $worksheets->write($row, $column, $value, $format_text);
                            }
                            $column ++;
                        }
                    }
                    $row ++;
                }
                $worksheets->setColumn(0, $column, 16);
            }
            $workbook->close();
        }
    }
    /**
     * 生成报表
     *
     * @param bool $pShowHeader 是否添加字段表头
     *
     * @return void
     */
    public function keyExport2 ($pShowHeader = false)
    {
        foreach ($this->_mDataValues as $workbook_name => $worksheet_array) {
            $workbook = new Spreadsheet_Excel_Writer();
            $workbook->send("{$workbook_name}.xls");
            if ($pShowHeader) {
                $format_header = &$workbook->addFormat();
                $format_header->setSize(12);
                $format_header->setFontFamily('宋体');
                $format_header->setFgColor('yellow');
                $format_header->setBorder(1);
                $format_header->setAlign('center');
                $format_text = &$workbook->addFormat();
                $format_text->setSize(12);
                $format_text->setFontFamily('宋体');
                $format_numeric = &$workbook->addFormat();
                $format_numeric->setSize(12);
                $format_numeric->setFontFamily('宋体');
                $format_numeric->setNumFormat('#,##0.00');
            }
            foreach ($worksheet_array as $worksheet_name => $row_array) {
                $worksheets = &$workbook->addWorksheet($worksheet_name);
                $row        = 0;
                foreach ($row_array as $column_array) {
                    $column = 0;
                    if ($pShowHeader && $row == 0) {
                        $row ++;
                    }
                    foreach ($column_array as $key => $value) {
                        if ($pShowHeader && $row == 1) {
                            if (isset($this->_mDataTitles)) {
                                $worksheets->write(0, $column, $this->_mDataTitles[$worksheet_name][$key], $format_header);
                            } else {
                                $worksheets->write(0, $column, $key, $format_header);
                            }
                        }
                        if (is_numeric($value)) {
                            $worksheets->write($row, $column, $value, $format_numeric);
                        } else {
                            $worksheets->write($row, $column, $value, $format_text);
                        }
                        $column ++;
                    }
                    $row ++;
                }
                $worksheets->setColumn(0, $column, 16);
            }
            $workbook->close();
        }
    }
}
?>