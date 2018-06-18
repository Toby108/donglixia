<?php
// +----------------------------------------------------------------------
// | Copyright (c) 2018-2018 http://www.donglixia.net All rights reserved.
// +----------------------------------------------------------------------
// | Author: 十万马 <962863675@qq.com>
// +----------------------------------------------------------------------
// | DateTime: 2018-02-09 16:17
// +----------------------------------------------------------------------

namespace app\admin_fussen\model;

use app\common\parent\Model;

class DataChangeLog extends Model
{
    public function formatData($data = [])
    {
        $res = [];
        foreach ($data as $k=>$v) {
            $content = !empty($v['content']) ? json_decode($v['content'], true) : '';

            $res[$k]['id'] = $v['id'];
            $res[$k]['table_name'] = $v['table_name'];
            $res[$k]['goods_name'] = !empty($content['goods_name']) ? $content['goods_name'] : '';
            $res[$k]['create_by'] = $v['create_by'];
            $res[$k]['create_time'] = $v['create_time'];
        }
        return $res;
    }

}