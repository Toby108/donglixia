<?php
// +----------------------------------------------------------------------
// | Copyright (c) 2018-2018 http://www.donglixia.net All rights reserved.
// +----------------------------------------------------------------------
// | Author: 十万马 <962863675@qq.com>
// +----------------------------------------------------------------------
// | DateTime: 2018-02-09 16:17
// +----------------------------------------------------------------------

// 应用公共（函数）文件

/**
 * 获取用户session
 * @param $key
 * @return mixed
 */
if (!function_exists('user_info')) {
    function user_info($key)
    {
        return \think\Session::get('userInfo.' . $key);
    }
}

/**
 * curl 接口请求
 * @param $url
 * @param array $data
 * @param array $config 头文件等配置信息
 * @return mixed
 */
if (!function_exists('http_curl')) {
    function http_curl($url, $data = [], $config = [])
    {
        $ch = curl_init(); //初始化，创建一个curl 资源
        //默认头文件
        $header = array(
            "Accept: application/json",
            "Cache-Control: no-cache",
            "Pragma: no-cache"
        );

        if (!empty($config['header'])) {
            $header = array_merge($header, $config['header']);//与个性化 头文件合并
        }
        $user_agent = !empty($config['user_agent']) ? $config['user_agent'] : 'Mozilla/5.0 (Linux; Android 8.0.0; MHA-AL00 Build/HUAWEIMHA-AL00; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/63.0.3239.111 Mobile Safari/537.36/1.0';
        $data = (!empty($config['type']) && $config['type'] == 'json') ? json_encode($data) : http_build_query($data);

        curl_setopt($ch, CURLOPT_URL, $url); //设置url
        curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); // 跳过证书检查
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE); // 从证书中检查SSL加密算法是否存在
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header); //请求时发送的header
        curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);//如果服务器超过该时间没有响应，脚本就会断开连接；
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);   //如果资源超过该时间没有完成返回，脚本将会断开连接。
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $ret = curl_exec($ch);//抓取url 并把它传递给浏览器
        curl_close($ch);//关闭curl资源，并且释放系统资源
        return $ret;
    }
}

/**
 * 将图片生成base64数据流
 * @param $image_file
 * @return string
 */
if (!function_exists('base64_encode_image')) {
    function base64_encode_image($image_file)
    {
        $image_info = getimagesize($image_file);
        $image_data = fread(fopen($image_file, 'r'), filesize($image_file));
        $base64_image = 'data:' . $image_info['mime'] . ';base64,' . chunk_split(base64_encode($image_data));
        return $base64_image;
    }
}

/**
 * 获取数组维度
 * @param $array
 * @return int 1为一维数组，2为二维数组
 */
if (!function_exists('array_depth')) {
    function array_depth($array)
    {
        if (!is_array($array)) return 0;
        $max_depth = 1;
        foreach ($array as $value) {
            if (is_array($value)) {
                $depth = array_depth($value) + 1;

                if ($depth > $max_depth) {
                    $max_depth = $depth;
                }
            }
        }
        return $max_depth;
    }
}

/**
 * 检查密码强度是否合格，连续四位数递增或递减，返回false
 * @param $str
 * @return bool
 */
if (!function_exists('password_strength')) {
    function password_strength($str)
    {
        //密码长度小于6位数，返回false
        if (strlen($str) < 6) {
            return false;
        }

        //密码中包含字母 或 特殊字符，返回true
        if (preg_match("/[a-z]+/", $str) || preg_match("/[A-Z]+/", $str) || preg_match("/[_|\-|+|=|*|!|@|#|$|%|^|&|(|)]+/", $str)) {
            return true;
        }

        $res = false;
        $str_arr = str_split($str);//将字符串拆分为单个字符
        $i = 0;
        foreach ($str_arr as $key => $val) {
            if (isset($str_arr[$key + 1]) &&
                (!((intval($str_arr[$key + 1]) - intval($val) == 0) || (intval($val) - intval($str_arr[$key + 1]) == 0))
                    && !((intval($str_arr[$key + 1]) - intval($val) == 1) || (intval($val) - intval($str_arr[$key + 1]) == 1)))) {
                $res = true;//当前字符与后一个字符相比较，差值非0、非1，则返回true
                break;
            } else {
                $i++;
                if ($i == 3) {
                    $res = false;//连续四个字符都不通过，则返回false
                    break;
                }
            }
        }
        return $res;
    }
}

/**
 * 删除文件资源
 * @param $url
 * @return bool
 */
if (!function_exists('delete_file')) {
    function delete_file($url)
    {
        $path = PUBLIC_PATH;
        if (!empty($url)) {
            if (file_exists($path . $url)) {
                $status = unlink($path . $url);
            } else {
                $status = true;
            }
        } else {
            $status = false;
        }
        return $status;
    }
}

/**
 * 输出由 - 拼接的共36位唯一字符串 UUID
 * @return string
 */
if (!function_exists('get_uuid')) {
    function get_uuid()
    {
        if (function_exists('com_create_guid')) {
            return com_create_guid();
        } else {
            mt_srand((double)microtime() * 10000);//optional for php 4.2.0 and up.
            $charid = strtoupper(md5(uniqid(rand(), true)));
            $hyphen = chr(45);// "-"
            $uuid = substr($charid, 0, 8) . $hyphen
                . substr($charid, 8, 4) . $hyphen
                . substr($charid, 12, 4) . $hyphen
                . substr($charid, 16, 4) . $hyphen
                . substr($charid, 20, 12);
            return $uuid;
        }
    }
}

/**
 * 生成36进制流水号
 * @param $value string 待累加流水号,如没有传空
 * @param $len int 生成流水号部位长度
 * @param string $title 累加字段前缀
 * @return bool|string
 */
if (!function_exists('serials_number')) {
    function serials_number($value, $len, $title = "")
    {
        $max = str_repeat("Z", $len);
        $org_title = substr($value, 0, strlen($title));
        $value = substr($value, strlen($title));
        if ($value && ($value == $max || strlen($value) !== $len || $org_title !== $title)) {
            return false;
        } else {
            if (empty($value)) {
                return strtoupper($title . sprintf("%0" . $len . "d", 1));
            } else {
                return strtoupper($title . sprintf("%0" . $len . "s", base_convert(intval(base_convert($value, 36, 10)) + 1, 10, 36)));
            }
        }
    }
}

/**
 * 导出成excel文件
 * @param array $content
 * $content = array(
 *          Tab_1  array('sheet'=>'sheet表名称',
 *                  'title' => array(列标题),
 *                  'content'=>array(列数据)
 *                 )
 *          Tab_2   array('sheet'=>'sheet表名称',
 *                  'title' => array(列标题),
 *                  'content'=>array(列数据)
 *                 )
 *           )
 * @param $fileName string 文件名
 * @param bool $SaveType true=输出到浏览器 false=保存到Public/Uploads
 *
 * @return string
 */
if (!function_exists('export_excel')) {
    function export_excel($content = [], $fileName, $SaveType = true)
    {
        include_once(VENDOR_PATH . 'phpoffice/phpexcel/Classes/PHPExcel.php');
        ini_set('memory_limit', '1024M');
        $objPHPExcel = new \PHPExcel();

        foreach ($content as $tab_id => $tabs) {
            // 设置sheet名称
            $objPHPExcel->createSheet();
            $objPHPExcel->setActiveSheetIndex($tab_id);
            if (!empty($tabs['sheet'])) {
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
            foreach ($tabs['content'] as $row_id => $row_value) {
                $cell = 0;
                foreach ($row_value as $key => $value) {
                    if (strtoupper($key) == 'ROW_NUMBER')
                        continue;

                    $cell_name = \PHPExcel_Cell::stringFromColumnIndex($cell++);

                    //单元格，数字大于15位，在前面加空格转化为文本
                    if (is_numeric($value) && strlen($value) > 15) {
                        $value = ' ' . $value;
                    }
                    $objPHPExcel->getActiveSheet()->setCellValue($cell_name . ($row_id + 2), $value);
                }
            }
        }

        if ($SaveType) {
            ob_end_clean();
            header('Content-Type: applicationnd.ms-excel;charset=utf-8');
            header('Content-Disposition: attachment;filename=' . $fileName . '.xlsx');
            header('Cache-Control: max-age=0');
            header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
            header('Pragma: public'); // HTTP/1.0
            $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
            $objWriter->save('php://output');
            exit();
        } else {
            $objWriter = new \PHPExcel_Writer_Excel2007($objPHPExcel);
            $objWriter->save(STATIC_PATH . "upload/" . iconv('UTF-8', 'GB2312', $fileName) . ".xlsx");
            return STATIC_PATH . "upload/" . $fileName . ".xlsx";
        }
    }
}

/**
 * excel文件导入成数据
 * +----------------------------------------------------------
 * Import Excel | 2013.08.23
 * Author:HongPing <hongping626@qq.com>
 * +----------------------------------------------------------
 *
 * @param $file upload
 *            file $_FILES
 *            +----------------------------------------------------------
 * @return array array("error","message")
 *         +----------------------------------------------------------
 */
if (!function_exists('import_excel')) {
    function import_excel($file)
    {
        if (!file_exists($file["tmp_name"])) {
            return ['error' => 0, 'message' => '未找到文件...'];
        }

        if ($file["type"] != 'application/vnd.ms-excel' && $file["type"] != 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet') {
            return ['error' => 0, 'message' => '不支持的文件格式...'];
        }

        ini_set("memory_limit", "-1");
        $_type = $file["type"] == 'application/vnd.ms-excel' ? 'Excel5' : 'Excel2007';

        include_once(VENDOR_PATH . 'phpoffice/phpexcel/Classes/PHPExcel/IOFactory.php');
        $objReader = \PHPExcel_IOFactory::createReader($_type);

        try {
            $PHPReader = $objReader->load($file["tmp_name"]);
            if (!isset($PHPReader))
                return ['error' => 0, 'message' => '读取错误!'];

            $allWorksheets = $PHPReader->getAllSheets();
            $i = 0;
            $temp = null;
            $array = [];
            foreach ($allWorksheets as $objWorksheet) {
                $sheetname = $objWorksheet->getTitle();
                $allRow = $objWorksheet->getHighestRow();
                $highestColumn = $objWorksheet->getHighestColumn();
                $allColumn = \PHPExcel_Cell::columnIndexFromString($highestColumn);
                $array[$i]["Title"] = $sheetname;
                $array[$i]["Cols"] = $allColumn;
                $array[$i]["Rows"] = $allRow;
                $arr = array();
                $isMergeCell = array();
                foreach ($objWorksheet->getMergeCells() as $cells) {
                    foreach (\PHPExcel_Cell::extractAllCellReferencesInRange($cells) as $cellReference) {
                        $isMergeCell[$cellReference] = true;
                    }
                }
                for ($currentRow = 1; $currentRow <= $allRow; $currentRow++) {
                    $row = array();
                    for ($currentColumn = 0; $currentColumn < $allColumn; $currentColumn++) {
                        ;
                        $cell = $objWorksheet->getCellByColumnAndRow($currentColumn, $currentRow);
                        $afCol = \PHPExcel_Cell::stringFromColumnIndex($currentColumn + 1);
                        $bfCol = \PHPExcel_Cell::stringFromColumnIndex($currentColumn - 1);
                        $col = \PHPExcel_Cell::stringFromColumnIndex($currentColumn);
                        $address = $col . $currentRow;
                        $value = $objWorksheet->getCell($address)->getValue();
                        if (substr($value, 0, 1) == '=') {
                            return ["error" => 0, "message" => '不能使用这个公式!'];
                        }
                        if ($cell->getDataType() == \PHPExcel_Cell_DataType::TYPE_NUMERIC) {
                            $cellstyleformat = $cell->getStyle($cell->getCoordinate())
                                ->getNumberFormat();
                            $formatcode = $cellstyleformat->getFormatCode();
                            if (preg_match('/^([$[A-Z]*-[0-9A-F]*])*[hmsdy]/i', $formatcode)) {
                                $value = gmdate("Y-m-d", \PHPExcel_Shared_Date::ExcelToPHP($value));
                            } else {
                                $value = \PHPExcel_Style_NumberFormat::toFormattedString($value, $formatcode);
                            }
                        }
                        if (isset($isMergeCell[$col . $currentRow]) && isset($isMergeCell[$afCol . $currentRow]) && !empty($value)) {
                            $temp = $value;
                        } elseif (isset($isMergeCell[$col . $currentRow]) && isset($isMergeCell[$col . ($currentRow - 1)]) && empty($value)) {
                            $value = $arr[$currentRow - 1][$currentColumn];
                        } elseif (isset($isMergeCell[$col . $currentRow]) && isset($isMergeCell[$bfCol . $currentRow]) && empty($value)) {
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
            unlink($file);
            return ['error' => 1, 'data' => $array];
        } catch (\Exception $e) {
            return ["error" => 0, "message" => $e->getMessage()];
        }
    }
}

/**
 * 判断是否微信环境
 * @return bool
 */
if (!function_exists('is_weixin')) {
    function is_weixin()
    {
        if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false) {
            return true;
        }
        return false;
    }
}

/**
 * 生成唯一订单号
 * @return string
 */
if (!function_exists('create_order_sn')) {
    function create_order_sn()
    {
        return date('Ymd') . substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
    }
}


/**
 * 处理图片，从临时文件夹转移到 img/** 文件夹
 * @param array $img temp文件夹中的图片路径集
 * @param string $folder
 * @return array
 */
if (!function_exists('imgTempFileMove')) {
    function imgTempFileMove($img = [], $folder = '')
    {
        $request = \think\Request::instance();
        $folder = !empty($folder) ? $folder : 'img/user/';//文件新目录
        foreach ($img as $k => $v) {
            //内容信息不为空，且确定为temp文件夹
            $v = str_replace($request->domain(), '', $v);
            $v = str_replace('//', '/', $v);
            if (!empty($v) && strpos($v, '/temp/')) {
                $img[$k] = str_replace('/img/temp/', '/' . $folder, $v);

                if (file_exists(PUBLIC_PATH . $v)) {
                    if (!is_dir(PUBLIC_PATH . dirname($img[$k]))) {
                        // 创建目录
                        mkdir(PUBLIC_PATH . dirname($img[$k]), 0777, true);
                    }

                    //转移图片文件，从 img/temp 文件夹，移到 img/** 文件夹中
                    copy(PUBLIC_PATH . $v, PUBLIC_PATH . $img[$k]);

                    //删除 img/temp 文件夹中对应的图片
                    delete_file($v);
                }
            }
        }
        return $img;
    }
}

/**
 * 设置系统配置缓存
 */
if (!function_exists('setSessionConfig')) {
    function setSessionConfig()
    {
        $data = \think\Db::name('system_config')->where('pid', '<>', 0)->field('id,view_name,code,value')->select();
        $config = [];
        foreach ($data as $k=>$v) {
            $config[$v['code']] = $v['value'];
        }
        session('config', $config);
    }
}

