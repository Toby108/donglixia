<?php
// +----------------------------------------------------------------------
// | Copyright (c) 2018-2018 http://www.donglixia.net All rights reserved.
// +----------------------------------------------------------------------
// | Author: 十万马 <962863675@qq.com>
// +----------------------------------------------------------------------
// | DateTime: 2018-02-09 16:17
// +----------------------------------------------------------------------
use think\Db;

// 应用公共（函数）文件

if (!function_exists('user_info')) {
    /**
     * 获取用户session
     * @param $key
     * @return mixed
     */
    function user_info($key)
    {
        return \think\Session::get('userInfo.' . $key);
    }
}

if (!function_exists('http_curl')) {
    /**
     * curl 接口请求
     * @param $url
     * @param array $data
     * @param array $config 头文件等配置信息
     * @return mixed
     */
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

if (!function_exists('base64_encode_image')) {
    /**
     * 将图片生成base64数据流
     * @param $image_file
     * @return string
     */
    function base64_encode_image($image_file)
    {
        $image_info = getimagesize($image_file);
        $image_data = fread(fopen($image_file, 'r'), filesize($image_file));
        $base64_image = 'data:' . $image_info['mime'] . ';base64,' . chunk_split(base64_encode($image_data));
        return $base64_image;
    }
}

if (!function_exists('array_depth')) {
    /**
     * 获取数组维度
     * @param $array
     * @return int 1为一维数组，2为二维数组
     */
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

if (!function_exists('get_child_ids')) {
    /**
     * 递归获取下级资料id集合
     * @param $id
     * @param $table_name
     * @param bool $merge
     * @return array
     */
    function get_child_ids($id, $table_name, $merge = true)
    {
        $id = explode(',', $id);
        $pk = Db::name($table_name)->getPk();//获取当前表主键
        $ids = Db::name($table_name)->whereIn('pid', $id)->column($pk);
        foreach ($ids as $k=>$v) {
            $ids = array_merge($ids, get_child_ids($v, $table_name, false));
        }
        if ($merge) $ids = array_merge($id, $ids);
        return $ids;
    }
}

if (!function_exists('get_parent_ids')) {
    /**
     * 递归获取上级资料id集合
     * @param $id
     * @param $table_name
     * @param bool $merge
     * @param array $res
     * @return array
     */
    function get_parent_ids($id, $table_name, $merge = true, &$res=[])
    {
        $pk = Db::name($table_name)->getPk();//获取当前表主键
        $pid = Db::name($table_name)->where($pk, $id)->value('pid');
        if (!empty($pid)){
            $res[] = $pid;
            get_parent_ids($pid, $table_name, false, $res);
        }
        if ($merge) array_push($res, $id);
        asort($res);
        return $res;
    }
}

if (!function_exists('password_strength')) {
    /**
     * 检查密码强度是否合格，连续四位数递增或递减，返回false
     * @param $str
     * @return bool
     */
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

if (!function_exists('delete_file')) {
    /**
     * 删除文件资源
     * @param $url
     * @return bool
     */
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

if (!function_exists('get_uuid')) {
    /**
     * 输出由 - 拼接的共36位唯一字符串 UUID
     * @return string
     */
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

if (!function_exists('serials_number')) {
    /**
     * 生成36进制流水号
     * @param $value string 待累加流水号,如没有传空
     * @param $len int 生成流水号部位长度
     * @param string $title 累加字段前缀
     * @return bool|string
     */
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

if (!function_exists('export_excel')) {
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

if (!function_exists('import_excel')) {
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

if (!function_exists('is_weixin')) {
    /**
     * 判断是否微信环境
     * @return bool
     */
    function is_weixin()
    {
        if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false) {
            return true;
        }
        return false;
    }
}

if (!function_exists('create_order_sn')) {
    /**
     * 生成唯一订单号
     * @return string
     */
    function create_order_sn()
    {
        return date('Ymd') . substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
    }
}

if (!function_exists('imgTempFileMove')) {
    /**
     * 处理图片，从临时文件夹转移到 img/** 文件夹
     * @param array $img temp文件夹中的图片路径集
     * @param string $folder
     * @return array
     */
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

if (!function_exists('setSessionConfig')) {
    /**
     * 设置系统配置缓存
     */
    function setSessionConfig()
    {
        $data = Db::name('system_config')->where('pid', '<>', 0)->field('id,view_name,sys_code,sys_value')->select();
        $config = [];
        foreach ($data as $k=>$v) {
            $config[$v['sys_code']] = $v['sys_value'];
        }
        session('config', $config);
    }
}

if (!function_exists('reset_sort')) {
    /**
     * 重新排序
     * @param $id
     * @param $table_name
     * @param string $type
     * @param string $field_sort
     * @return array|false|PDOStatement|string|\think\Collection
     */
    function reset_sort($id, $table_name, $type = 'asc', $field_sort = 'sort_num')
    {
        $pk = Db::name($table_name)->getPk();//获取当前表主键字段名
        //判断是否存在'pid'，若存在，则只取同级别数据
        if (has_field($table_name, 'pid')) {
            $map['pid'] = Db::name($table_name)->where($pk, $id)->value('pid');
        } elseif (has_field($table_name, 'language')) {
            $map['language'] = session('config.language');
        } else {
            $map[] = ['exp', '1=1'];
        }
        $data = Db::name($table_name)->where($map)->field($pk . ',' . $field_sort)->order($field_sort . ',' . $pk . ' asc')->select();

        //将序号重新按1开始排序
        foreach ($data as $key => $val) {
            $data[$key][$field_sort] = $key + 1;
        }
        //处理更改排序操作
        foreach ($data as $key => $val) {
            if ($type == 'asc') {
                if (($key == '0') && $val[$pk] == $id) {
                    break;//首位菜单 点升序，直接中断
                }
                //升序操作：当前菜单序号减一，前一位的序号加一
                if ($val[$pk] == $id) {
                    $data[$key - 1][$field_sort]++;
                    $data[$key][$field_sort]--;
                    break;
                }
            } elseif ($type == 'desc') {
                if (($key == count($data)) && $val[$pk] == $id) {
                    break;//末位菜单 点降序，直接中断
                }
                //降序操作：当前菜单序号加一，后一位的序号减一
                if ($val[$pk] == $id && isset($data[$key + 1])) {
                    $data[$key][$field_sort]++;
                    $data[$key + 1][$field_sort]--;
                    break;
                }
            }
        }
        return !empty($data) ? $data : [];
    }
}

if (!function_exists('has_field')) {
    /**
     * 判断数据表是否存在该字段
     * @param $table_name
     * @param $field
     * @return bool
     */
    function has_field($table_name, $field)
    {
        $field_list = Db::name($table_name)->getTableFields();
        return in_array($field, $field_list);
    }
}

if (!function_exists('delete_file_by_time')) {
    /**
     * 递归删除目录下，某一个时间点之前的文件
     * @param $dir string 目录路径
     * @param $time int 小时 72h=3天
     */
    function delete_file_by_time($dir, $time = 72)
    {
        //判断是否目录是否存在
        if (is_dir($dir)) {
            // 打开目录，然后读取其内容
            if ($dh = opendir($dir)) {
                while (($file = readdir($dh)) !== false) {
                    //file 为目录下其中一个文件的文件名
                    if ($file != "." && $file != "..") {
                        $fullpath = $dir . "/" . $file;
                        if (!is_dir($fullpath)) {
                            if ((time() - filemtime($fullpath)) / 3600 > $time) {
                                unlink($fullpath);
                            }
                        } else {
                            delete_file_by_time($fullpath, $time);
                        }
                    }
                }
            }
            closedir($dh);
        }
    }
}

if (!function_exists('array_sequence')) {
    /**
     * 二维数组根据某个字段进行排序
     * @param array $array 数组
     * @param string $field 数组下标
     * @param string $sort 顺序标志 SORT_DESC 降序；SORT_ASC 升序
     * @param string $sort
     * @return mixed
     */
    function array_sequence($array = [], $field, $sort = 'SORT_DESC')
    {
        if (!empty($array)) {
            $arrSort = array();
            foreach ($array as $uniqid => $row) {
                foreach ($row as $key => $value) {
                    $arrSort[$key][$uniqid] = $value;
                }
            }
            array_multisort($arrSort[$field], constant($sort), $array);
        }
        return $array;
    }
}

if (!function_exists('sock_open')) {
    /**
     *  远程请求（不获取内容）函数，非阻塞
     * @param $url
     * @return array
     */
    function sock_open($url) {
        $host = parse_url($url,PHP_URL_HOST);
        $port = parse_url($url,PHP_URL_PORT);
        $port = $port ? $port : 80;
        $scheme = parse_url($url,PHP_URL_SCHEME);
        $path = parse_url($url,PHP_URL_PATH);
        $query = parse_url($url,PHP_URL_QUERY);
        if($query) $path .= '?'.$query;
        if($scheme == 'https') {
            $host = 'ssl://'.$host;
        }

        $fp = fsockopen($host,$port,$error_code,$error_msg,1);
        if(!$fp) {
            return array('error_code' => $error_code,'error_msg' => $error_msg);
        }
        else {
            stream_set_blocking($fp,true);//开启非阻塞模式
            stream_set_timeout($fp,1);//设置超时
            $header = "GET $path HTTP/1.1\r\n";
            $header.="Host: $host\r\n";
            $header.="Connection: close\r\n\r\n";//长连接关闭
            fwrite($fp, $header);
            usleep(1000); // 这一句也是关键，如果没有这延时，可能在nginx服务器上就无法执行成功
            fclose($fp);
            return array('error_code' => 0);
        }
    }
}

/**
 * 系统邮件发送函数
 * @param string $to_email 接收邮件者邮箱
 * @param string $title 邮件标题
 * @param string $body 邮件内容
 * @param string $attachment 附件列表
 * @return boolean
 * @author static7 <static7@qq.com>
 */
function send_mail($to_email, $title = '成了！', $body = 'Nice to meet you!', $attachment = null)
{
    //获取系统配置信息
    $pid = Db::name('system_config')->where('sys_code', 'email_config')->value('id');
    $config = Db::name('system_config')->where('pid', $pid)->column('sys_code,sys_value');
    if (empty($config)) {
        return '邮件配置信息为空！';
    }

    if (empty($config['email_enable'])) {
        return '邮件服务已禁用！';
    }

    $smtp_host = !empty($config['smtp_host']) ? $config['smtp_host'] : '';
    $smtp_port = !empty($config['smtp_port']) ? $config['smtp_port'] : '';
    $smtp_password = !empty($config['smtp_password']) ? $config['smtp_password'] : '';
    $email_send = !empty($config['email_send']) ? $config['email_send'] : '';
    $email_nick = !empty($config['email_nick']) ? $config['email_nick'] : '';

    $mail = new \PHPMailer\PHPMailer\PHPMailer();           //实例化PHPMailer对象
    $mail->CharSet = 'UTF-8';           //设定邮件编码，默认ISO-8859-1，如果发中文此项必须设置，否则乱码
    $mail->IsSMTP();                    // 设定使用SMTP服务
    $mail->SMTPDebug = 0;               // SMTP调试功能 0=关闭 1 = 错误和消息 2 = 消息
    $mail->SMTPAuth = true;             // 启用 SMTP 验证功能
    $mail->SMTPSecure = 'ssl';          // 使用安全协议
    $mail->Host = $smtp_host; // SMTP 服务器
    $mail->Port = $smtp_port;                  // SMTP服务器的端口号
    $mail->Username = $email_send;    // SMTP服务器用户名
    $mail->Password = $smtp_password;     // SMTP服务器密码
    $mail->SetFrom($email_send, $email_nick);//发件人邮箱，发件人昵称
    $replyEmail = '';                   //留空则为发件人EMAIL
    $replyName = '';                    //回复名称（留空则为发件人名称）
    $mail->AddReplyTo($replyEmail, $replyName);
    $mail->Subject = $title;
    $mail->MsgHTML($body);
    $mail->AddAddress($to_email);
    if (is_array($attachment)) { // 添加附件
        foreach ($attachment as $file) {
            is_file($file) && $mail->AddAttachment($file);
        }
    }
    return $mail->Send() ? true : $mail->ErrorInfo;
}

if (!function_exists('log_write')) {
    /**
     *日志记录，按照"Ymd.log"生成当天日志文件
     * 日志路径为：入口文件所在目录/logs/$type/当天日期.log.php，例如 /logs/error/20120105.log.php
     * @param string $type 日志类型，对应logs目录下的子文件夹名
     * @param string $content 日志内容
     * @return bool true/false 写入成功则返回true
     */
    function log_write($type = "info", $content = "")
    {
        if (!$content || !$type) {
            return FALSE;
        }
        $dir = STATIC_PATH . DS . 'logs' . DS . $type;
        if (!is_dir($dir)) {
            if (!mkdir($dir, 0777, true)) {
                return false;
            }
        }
        $filename = $dir . DS . date("Ymd", time()) . '.log';
        if (file_exists($filename)) {
            $logs = include $filename;
            if ($logs && !is_array($logs)) {
                unlink($filename);
                $logs = [];
            }
        }

        $logs[] = array("time" => date("Y-m-d H:i:s"), "content" => $content);
        $str = "<?php \r\n return " . var_export($logs, true) . ";";
        if (!$fp = @fopen($filename, "wb")) {
            return false;
        }
        if (!fwrite($fp, $str)) return false;
        fclose($fp);
        return true;
    }
}

if (!function_exists('log_read')) {
    /**
     * 获取日志内容
     * @param string  $file 文件名路径
     * @return array
     */
    function log_read($file)
    {
        $file = !empty($file) ? $file : STATIC_PATH . '/logs/info/'. date("Ymd", time()) . '.log';
        if (file_exists($file)) {
            $logs = include $file;
            if ($logs && !is_array($logs)) {
                unlink($file);
            }
        }
        return !empty($logs) && is_array($logs) ? $logs : [];
    }
}

if (!function_exists('save_task_log')) {
    /**
     * 保存定时任务日志
     * @param $remark string 备注
     * @param $is_success int 是否成功
     * @param $task_name string 任务名|方法名
     */
    function save_task_log($remark, $is_success = 1, $task_name = '')
    {
        $request = \think\Request::instance();
        $data['url'] =  $request->url(true);
        $data['ip'] =  !empty($request->ip(0)) ? $request->ip(0) : 0;
        $data['referer'] =  !empty($request->server('HTTP_REFERER')) ? $request->server('HTTP_REFERER') : '';
        $data['user_agent'] =  !empty($request->header('user-agent')) ? $request->header('user-agent') : '';
        $data['task_name'] = $task_name;
        $data['remark'] = $remark;
        $data['is_success'] = $is_success;
        $data['create_time'] = date('Y-m-d H:i:s');
        try{
            Db::name('task_log')->insert($data);
        }
        catch (\Exception $e) {
            save_error_log($e->getMessage());
        }
    }
}

if (!function_exists('save_error_log')) {
    /**
     * 保存错误日志
     * @param $content
     */
    function save_error_log($content)
    {
        $request = \think\Request::instance();
        $data['user_id'] = user_info('user_id') ? user_info('user_id') : 0;
        $data['url'] =  $request->url(true);
        $data['method'] = $request->method();
        $data['content'] = $content;
        $data['create_time'] = date('Y-m-d H:i:s');
        try{
            Db::name('error_log')->insert($data);
            //给超级管理员发送站内信
            send_message(['title'=>'系统新错误，请查看错误日志', 'type'=>2, 'role_id'=>1]);
            send_mail('962863675@qq.com');//发送邮件通知
        }
        catch (\Exception $e) {
            Db::name('error_log')->insert(['content' => '本表信息保存失败：'.$e->getMessage().'; '.json_encode($data), 'create_time'=>date('Y-m-d H:i:s')]);
        }
    }
}

if (!function_exists('send_message')) {
    /**
     * 发送站内信
     * @param array $data
     * @return array|bool
     */
    function send_message($data = [])
    {
        if (empty($data['receive_id']) && empty($data['role_id']) && empty($data['dept_id'])) {
            return ['status'=>false, 'msg'=>'请指定接收者'];
        }
        try{
            $map = [];
            if (!empty($data['receive_id'])) {
                $map['user_id'] = $data['receive_id'];
            }
            if (!empty($data['role_id'])) {
                $map['role_id'] = $data['role_id'];
            }
            if (!empty($data['dept_id'])) {
                $map['dept_id'] = $data['dept_id'];
            }
            $user_ids = Db::name('user')->where($map)->column('user_id');//获取接收者id

            if (empty($user_ids)) {
                return ['status'=>false, 'msg'=>'接收者不明确'];
            }
            $request = \think\Request::instance();
            $saveData['url'] =  $request->url(true);
            $saveData['title'] = !empty($data['title']) ? $data['title'] : '';
            $saveData['content'] = !empty($data['content']) ? $data['content'] : '';
            $saveData['type'] = !empty($data['type']) ? $data['type'] : 1;//通知类型：1系统消息 ，2系统公告，3新发布
            $saveData['device'] = !empty($data['device']) ? $data['device'] : 0;//设备类型：0不区分，1客户端站内信,2后台站内信
            $saveData['create_by'] = user_info('user_id') ? user_info('user_id') : 1;
            $saveData['create_time'] = time();

            $id = Db::name('user_message')->insertGetId($saveData);//插入消息主表
            $saveList = [];
            foreach ($user_ids as $k=>$v) {
                $saveList[$k]['user_id'] = $v;
                $saveList[$k]['msg_id'] = $id;
            }
            Db::name('user_message_list')->insertAll($saveList);//插入发送列表
        }
        catch (\Exception $e) {
            Db::name('error_log')->insert(['content' => $e->getMessage().'; '.json_encode($data), 'create_time'=>date('Y-m-d H:i:s')]);
        }
        return true;
    }
}


