<?php
// +----------------------------------------------------------------------
// | Copyright (c) 2018-2018 http://www.donglixia.net All rights reserved.
// +----------------------------------------------------------------------
// | Author: 十万马 <962863675@qq.com>
// +----------------------------------------------------------------------
// | DateTime: 2018-02-09 16:17
// +----------------------------------------------------------------------

namespace app\admin_fussen\model;
use app\common\model\Base;
use think\Db;

class SystemConfig extends Base
{
    /**
     * 解析取值范围
     * @param $value
     * @return mixed|string
     */
    public function getValueRangeAttr($value)
    {
        return !empty($value) ? json_decode($value, true) : '';
    }

    /**
     * 获取数据列表,以child分组
     * @return array
     */
    public function getDataList()
    {
        $list = $this->field('id,pid,view_name,sys_code,sys_value,value_range,sort_num,describe,type')->order('sort_num asc')->select();
        return \Tree::getTree($list);
    }

}