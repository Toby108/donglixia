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
     * 获取文章状态，对应的中文名称
     * @param $value
     * @param $data
     * @return mixed
     */
    public function getStateTextAttr($value, $data)
    {
        return Db::name('basic_info')->where('basic_id', $data['state'])->value('name');
    }

    /**
     * 获取分类，对应的中文名称
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
     * 保存时间戳，发布时间
     * @param $value
     * @return mixed
     */
    public function setPublicTimeAttr($value)
    {
        return !empty($value) ? strtotime($value) : time();
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

}