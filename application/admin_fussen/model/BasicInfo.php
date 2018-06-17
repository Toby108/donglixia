<?php
// +----------------------------------------------------------------------
// | Copyright (c) 2018-2018 http://www.donglixia.net All rights reserved.
// +----------------------------------------------------------------------
// | Author: 十万马 <962863675@qq.com>
// +----------------------------------------------------------------------
// | DateTime: 2018-02-09 16:17
// +----------------------------------------------------------------------

namespace app\admin_fussen\model;

use app\common\model\BasicInfo as ComBasicInfo;
use think\Db;

class BasicInfo extends ComBasicInfo
{
    /**
     * 获取“是否启用”字段，中文名称
     * @param $value
     * @return string
     */
    protected function getStatusTextAttr($value)
    {
        return $value == '1' ? '启用' : '禁用';
    }

    /**
     * 获取“是否系统预设值”字段，中文名称
     * @param $value
     * @param $data
     * @return string
     */
    protected function getIsSystemTextAttr($value, $data)
    {
        return $data['status'] == '1' ? '是' : '否';
    }

    /**
     * 获取“pid”字段，中文名称
     * @param $value
     * @return mixed
     */
    protected function getPidTextAttr($value)
    {
        return !empty($value) ? $this->where('basic_id', $value)->value('name') : '顶级';
    }

    /**
     * 获取人员基本资料
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function getPersonList()
    {
        $basic_ids = $this->where('cat_code', 'person')->column('basic_id');//人员资料id
        return $this->whereIn('pid', $basic_ids)->field('basic_id,pid,cat_code,code,name')->order('sort_num')->select();
    }

    /**
     * 获取某一资料列表
     * @param $cat_code
     * @param $code
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function getBasicList($cat_code, $code)
    {
        $basic_pid = Db::name('basic_info')->where('cat_code', $cat_code)->where('code', $code)->value('basic_id');
        return Db::name('basic_info')->where('pid', $basic_pid)->field('basic_id,name')->order('sort_num')->select();
    }

}