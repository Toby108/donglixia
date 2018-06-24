<?php
// +----------------------------------------------------------------------
// | Copyright (c) 2018-2018 http://www.donglixia.net All rights reserved.
// +----------------------------------------------------------------------
// | Author: 十万马 <962863675@qq.com>
// +----------------------------------------------------------------------
// | DateTime: 2018-02-09 16:17
// +----------------------------------------------------------------------

namespace app\admin_fussen\model;

use app\common\model\Goods as ComGoods;
use think\Db;

class Goods extends ComGoods
{
    /**
     * 获取重要等级，对应的中文名称
     * @param $value
     * @param $data
     * @return mixed
     */
    public function getLevelTextAttr($value, $data)
    {
        return Db::name('basic_info')->where('basic_id', $data['level'])->value('basic_name');
    }

    /**
     * 获取类目，对应的中文名称
     * @param $value
     * @param $data
     * @return mixed
     */
    public function getCatNameAttr($value, $data)
    {
        return Db::name('goods_cat')->where('cat_id', $data['cat_id'])->value('cat_name');
    }

    /**
     * 格式化发布时间
     * @param $value
     * @param $data
     * @return mixed
     */
    public function getPublicDateAttr($value, $data)
    {
        return !empty($data['public_time']) ? date('Y-m-d H:i', $data['public_time']) : '';
    }

    /**
     * 格式化发布时间
     * @param $value
     * @param $data
     * @return mixed
     */
    public function getPublicDateHhIiSsAttr($value, $data)
    {
        return !empty($data['public_time']) ? date('Y-m-d H:i:s', $data['public_time']) : '';
    }

    /**
     * 保存“产品详情”，html转义
     * @param $value
     * @return mixed
     */
    public function setDetailAttr($value)
    {
        return !empty($value) ? htmlspecialchars($value) : '';
    }

    /**
     * 获取“产品详情”，html转义
     * @param $value
     * @return mixed
     */
    public function getDetailAttr($value)
    {
        return !empty($value) ? htmlspecialchars_decode($value) : '';
    }

    /**
     * 保存“产品类目”
     * @param $value
     * @return mixed
     */
    public function setCatIdAttr($value)
    {
        if (!empty($value)) {
            $arr = explode('/', $value);
        }
        return !empty($arr) ? end($arr) : 0;
    }

    /**
     * 保存“扩展类目”
     * @param $value
     * @return mixed
     */
    public function setCatIdExtAttr($value)
    {
        $res = [];
        if (!empty($value)) {
            $arr = explode(',', $value);
            foreach ($arr as $k=>$v) {
                $temp = explode('/', $v);
                $res[$k] = end($temp);
            }
        }
        return implode(',', $res);
    }

    /**
     * 获取产品类目下拉列表
     */
    public function getCatTree()
    {
        $catList = Db::name('goods_cat')->where('state', 1)->field('cat_id as value,pid,cat_name as name')->select();
        return \Tree::getTree($catList, 'value', 'pid', 'children');
    }

}