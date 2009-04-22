<?php
/**
* @author 马涛 <matao@bj.tom.com>
* @version v 1.0 2004/04/16
* @package Core_Class
*/

/**
* Excel 报表生成类
*/

class KISS_Util_Excel {
    var $mChartTitle;
    var $mDataTitles;
    var $mDataValues;
    
    function setDataTitles($pDataTitles) {
        $this->mDataTitles = $pDataTitles;
    }
    
    function setDataValues($pDataValues) {
        $this->mDataValues = $pDataValues;
    }
    
    function simpleExport()    {
        $workbook = new Spreadsheet_Excel_Writer();
        $workbook->send('test.xls');
        
        for($i = 0;$i < count($this->mDataValues);$i++) {
            $worksheets = &$workbook->addWorksheet();
            for($j = 0;$j < count($this->mDataValues[$i]);$j++) {
                for($k = 0;$k < count($this->mDataValues[$i][$j]);$k++) {
                    $worksheets->write(0, $k, $this->mDataTitles[$i][$k]);
                    $worksheets->write($j + 1, $k, $this->mDataValues[$i][$j][$k]);
                }
            }
        }
        $workbook->close();
    }
    
    function keyExport($pShowHeader = false) {
        foreach($this->mDataValues as $workbook_name => $worksheet_array) {
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
                
                $format['text'] = &$format_text;
                $format['numeric'] = &$format_numeric;
                $format['numeric0'] = &$format_numeric0;
                $format['numeric1'] = &$format_numeric1;
            }
            
            foreach($worksheet_array as $worksheet_name => $row_array) {
                $worksheets = &$workbook->addWorksheet($worksheet_name);
                $row = 0;
                foreach($row_array as $row_name => $column_array) {
                    if(is_array($this->mDataTitles[$worksheet_name][0])) {
                        if ($pShowHeader && $row == 0) {
                            for($column = 0;$column < count($this->mDataTitles[$worksheet_name]);$column ++) {
                                $worksheets->write(0, $column, $this->mDataTitles[$worksheet_name][$column]['title'], $format_header);
                            }
                            $row ++;
                        }
                        for($column = 0;$column < count($this->mDataTitles[$worksheet_name]);$column ++) {
                            $worksheets->write($row, $column, $column_array[$this->mDataTitles[$worksheet_name][$column]['key']], $format[$this->mDataTitles[$worksheet_name][$column]['type']]);
                        }
                    }
                    else {
                        $column = 0;
                        if ($pShowHeader && $row == 0) {
                            $row ++;
                        }
                        foreach($column_array as $key => $value) {
                            if ($pShowHeader && $row == 1) {
                                if(isset($this->mDataTitles))
                                {
                                    $worksheets->write(0, $column, $this->mDataTitles[$worksheet_name][$key], $format_header);
                                }
                                else
                                {
                                    $worksheets->write(0, $column, $key, $format_header);
                                }
                            }
                            if (is_numeric($value))
                            $worksheets->write($row, $column, $value, $format_numeric);
                            else
                            $worksheets->write($row, $column, $value, $format_text);
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
    
    function keyExport2($pShowHeader = false) {
        foreach($this->mDataValues as $workbook_name => $worksheet_array) {
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
            
            foreach($worksheet_array as $worksheet_name => $row_array) {
                $worksheets = &$workbook->addWorksheet($worksheet_name);
                $row = 0;
                foreach($row_array as $row_name => $column_array) {
                    $column = 0;
                    if ($pShowHeader && $row == 0) {
                        $row ++;
                    }
                    foreach($column_array as $key => $value) {
                        if ($pShowHeader && $row == 1) {
                            if(isset($this->mDataTitles))
                            {
                                $worksheets->write(0, $column, $this->mDataTitles[$worksheet_name][$key], $format_header);
                            }
                            else
                            {
                                $worksheets->write(0, $column, $key, $format_header);
                            }
                        }
                        if (is_numeric($value))
                        $worksheets->write($row, $column, $value, $format_numeric);
                        else
                        $worksheets->write($row, $column, $value, $format_text);
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