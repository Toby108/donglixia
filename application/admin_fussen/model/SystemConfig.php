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
use think\Db;

class SystemConfig extends Model
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
     * 获取网站信息列表
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function getWebInfoList()
    {
        $wen_info_id = Db::name('system_config')->where('code', 'web_info')->value('id');
        return $this->getPidData($wen_info_id);
    }

    /**
     * 获取系统设置信息列表
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function getSystemConfigList()
    {
        $system_config_id = Db::name('system_config')->where('code', 'system_config')->value('id');
        return $this->getPidData($system_config_id);
    }

    /**
     * 根据pid获取数据列表
     * @param $pid
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function getPidData($pid)
    {
        return $this->where('pid', $pid)
            ->field('id,pid,view_name,code,value,value_range,sort_num,describe,type')
            ->order('sort_num')
            ->select();
    }
}