<?php
// +----------------------------------------------------------------------
// | Copyright (c) 2018-2018 http://www.donglixia.net All rights reserved.
// +----------------------------------------------------------------------
// | Author: 十万马 <962863675@qq.com>
// +----------------------------------------------------------------------
// | DateTime: 2018-02-09 16:17
// +----------------------------------------------------------------------

namespace app\common\parent;

use think\Controller as CoreController;

class Controller extends CoreController
{
    protected $result = array();

    /**
     * @param array $content
     *         $content = array(
     *          Tab_1  array('sheet'=>'sheet表名称',
     *                  'title' => array(列标题),
     *                  'content'=>array(列数据)
     *                 )
     *          Tab_2   array('sheet'=>'sheet表名称',
     *                  'title' => array(列标题),
     *                  'content'=>array(列数据)
     *                 )
     *           )
     * @param string $fileName 文件名
     * @param bool $SaveType true=输出到浏览器 false=保存到目录并返回地址
     * @return string
     */
    public function exportExcel($content = array(), $fileName, $SaveType = true)
    {
        $objPHPExcel = new \PHPExcel();
        foreach ($content as $tab_id => $tabs) {
            // 设置sheet名称
            $objPHPExcel->createSheet();
            $objPHPExcel->setActiveSheetIndex($tab_id);
            if(!empty($tabs['sheet']))
            {
                $objPHPExcel->getActiveSheet()->setTitle($tabs['sheet']);
            }

            // 写入标题
            foreach ($tabs['title'] as $title_id => $title) {
                $title_name = \PHPExcel_Cell::stringFromColumnIndex($title_id);
                $objPHPExcel->getActiveSheet()->setCellValue($title_name . '1', $title);
                $objPHPExcel->getActiveSheet()
                    ->getStyle($title_name . '1')
                    ->getFont()
                    ->setBold(true);
            }

            // 写入数据
            if (!empty($tabs['content'])){
                foreach ($tabs['content'] as $row_id => $row_value) {
                    $cell = 0;
                    foreach ($row_value as $key => $value) {
                        if (strtoupper($key) == 'ROW_NUMBER')
                            continue;

                        $cell_name = \PHPExcel_Cell::stringFromColumnIndex($cell ++);
                        $objPHPExcel->getActiveSheet()->setCellValue($cell_name . ($row_id + 2), $value);
                    }
                }
            }
        }

        if ($SaveType) {
            ob_end_clean();
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename='.$fileName.'.xlsx');
            header('Cache-Control: max-age=0');
            $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
            $objWriter->save('php://output');
            exit();
        } else {
            $objWriter = new \PHPExcel_Writer_Excel2007($objPHPExcel);
            $objWriter->save($_SERVER['DOCUMENT_ROOT'] . "static/file/" . iconv('UTF-8', 'GB2312', $fileName) . ".xlsx");
            return $_SERVER['DOCUMENT_ROOT'] . "static/file/" . $fileName . ".xlsx";
        }
    }

    public function importExecl($file)
    {
        if (!file_exists($file["tmp_name"])) {
            return array(
                "error" => 0,
                'message' => '未找到文件...'
            );
        }

        if ($file["type"] != 'application/vnd.ms-excel' && $file["type"] != 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
        {
            return array(
                "error" => 0,
                'message' => '不支持的文件格式...'
            );
        }

        ini_set("memory_limit", "-1");
        $_type = $file["type"] == 'application/vnd.ms-excel' ? 'Excel5' : 'Excel2007';

        $objReader = \PHPExcel_IOFactory::createReader($_type);
        $PHPReader = $objReader->load($file["tmp_name"]);

        if (!isset($PHPReader))
        {
            return array(
                "error" => 0,
                'message' => '读取错误!'
            );
        }

        $allWorksheets = $PHPReader->getAllSheets();
        $i = 0;
        $temp = null;
        $array = [];
        foreach ($allWorksheets as $objWorksheet)
        {
            $sheetName = $objWorksheet->getTitle();
            $allRow = $objWorksheet->getHighestRow(); // how many rows
            $highestColumn = $objWorksheet->getHighestColumn(); // how many columns
            $allColumn = \PHPExcel_Cell::columnIndexFromString($highestColumn);
            $array[$i]["Title"] = $sheetName;
            $array[$i]["Cols"] = $allColumn;
            $array[$i]["Rows"] = $allRow;
            $arr = array();
            $isMergeCell = array();
            foreach ($objWorksheet->getMergeCells() as $cells)
            {
                foreach (\PHPExcel_Cell::extractAllCellReferencesInRange($cells) as $cellReference)
                {
                    $isMergeCell[$cellReference] = true;
                }
            }
            for ($currentRow = 1; $currentRow <= $allRow; $currentRow ++)
            {
                $row = array();
                for ($currentColumn = 0; $currentColumn < $allColumn; $currentColumn++)
                {
                    $cell = $objWorksheet->getCellByColumnAndRow($currentColumn, $currentRow);
                    $afCol = \PHPExcel_Cell::stringFromColumnIndex($currentColumn + 1);
                    $bfCol = \PHPExcel_Cell::stringFromColumnIndex($currentColumn - 1);
                    $col = \PHPExcel_Cell::stringFromColumnIndex($currentColumn);
                    $address = $col . $currentRow;
                    $value = $objWorksheet->getCell($address)->getValue();
                    if (substr($value, 0, 1) == '=')
                    {
                        return array(
                            "error" => 0,
                            'message' => '不能使用这个公式!'
                        );
                    }
                    if(is_object($value)){
                        $value= $value->__toString();
                    }
                    if ($cell->getDataType() == \PHPExcel_Cell_DataType::TYPE_NUMERIC)
                    {
                        $cellStyleFormat = $cell->getStyle($cell->getCoordinate())->getNumberFormat();
                        $formatCode = $cellStyleFormat->getFormatCode();
                        if (preg_match('/^([$[A-Z]*-[0-9A-F]*])*[hmsdy]/i', $formatCode))
                        {
                            $value = gmdate("Y-m-d", \PHPExcel_Shared_Date::ExcelToPHP($value));
                        }
                        else
                        {
                            $value = \PHPExcel_Style_NumberFormat::toFormattedString($value, $formatCode);
                        }
                    }
                    if (isset($isMergeCell[$col . $currentRow]) && isset($isMergeCell[$afCol . $currentRow]) && !empty($value))
                    {
                        $temp = $value;
                    }
                    elseif (isset($isMergeCell[$col . $currentRow]) && isset($isMergeCell[$col . ($currentRow - 1)]) && empty($value))
                    {
                        $value = $arr[$currentRow - 1][$currentColumn];
                    }
                    elseif (isset($isMergeCell[$col . $currentRow]) && isset($isMergeCell[$bfCol . $currentRow]) && empty($value))
                    {
                        $value = $temp;
                    }
                    $row[$currentColumn] = $value;
                }
                $arr[$currentRow] = $row;
            }
            $array[$i]["Content"] = $arr;
            $i++;
        }
        unset($objWorksheet);
        unset($PHPReader);
        unset($PHPExcel);
        unlink($file["tmp_name"]);
        return array(
            "error" => 1,
            "data" => $array
        );
    }

}