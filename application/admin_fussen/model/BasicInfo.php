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
        return $this->whereIn('pid', $basic_ids)->field('basic_id,pid,cat_code,code,name')->select();
    }

    /**
     * 重新排序
     * @param $id
     * @param $type
     * @return mixed
     */
    public function resetSort($id, $type)
    {
        //获取同级别的菜单数据
        $pid = Db::name('basic_info')->where('basic_id', $id)->value('pid');
        $data = Db::name('basic_info')->where('pid', $pid)->field('basic_id,sort')->order('sort,basic_id asc')->select();

        //将序号重新按1开始排序
        foreach ($data as $key => $val) {
            $data[$key]['sort'] = $key+1;
        }
        //处理更改排序操作
        foreach ($data as $key => $val) {
            if ($type == 'asc') {
                if (($key == '0') && $val['basic_id'] == $id) {
                    break;//首位菜单 点升序，直接中断
                }
                //升序操作：当前菜单序号减一，前一位的序号加一
                if ($val['basic_id'] == $id) {
                    $data[$key-1]['sort']++;
                    $data[$key]['sort']--;
                    break;
                }
            } elseif ($type == 'desc') {
                if (($key == count($data)) && $val['basic_id'] == $id) {
                    break;//末位菜单 点降序，直接中断
                }
                //降序操作：当前菜单序号加一，后一位的序号减一
                if ($val['basic_id'] == $id && isset($data[$key+1])) {
                    $data[$key]['sort']++;
                    $data[$key+1]['sort']--;
                    break;
                }
            }
        }
        return $data;
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
        return Db::name('basic_info')->where('pid', $basic_pid)->field('basic_id,name')->order('sort')->select();
    }

}