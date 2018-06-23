<?php
// +----------------------------------------------------------------------
// | Copyright (c) 2018-2018 http://www.donglixia.net All rights reserved.
// +----------------------------------------------------------------------
// | Author: 十万马 <962863675@qq.com>
// +----------------------------------------------------------------------
// | DateTime: 2018-02-09 16:17
// +----------------------------------------------------------------------

namespace app\admin_fussen\model;

use app\common\model\Article as ComArticle;
use think\Db;

class Article extends ComArticle
{
    /**
     * 获取文章状态，对应的中文名称
     * @param $value
     * @param $data
     * @return mixed
     */
    public function getTypeTextAttr($value, $data)
    {
        return Db::name('basic_info')->where('basic_id', $data['type'])->value('basic_name');
    }

    /**
     * 获取栏目，对应的中文名称
     * @param $value
     * @param $data
     * @return mixed
     */
    public function getCatNameAttr($value, $data)
    {
        return Db::name('article_cat')->where('cat_id', $data['cat_id'])->value('cat_name');
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
     * 保存“文章详情”，html转义
     * @param $value
     * @return mixed
     */
    public function setContentAttr($value)
    {
        return !empty($value) ? htmlspecialchars($value) : '';
    }

    /**
     * 保存“文章栏目”
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
     * 获取“文章详情”，html转义
     * @param $value
     * @return mixed
     */
    public function getContentAttr($value)
    {
        return !empty($value) ? htmlspecialchars_decode($value) : '';
    }

    /**
     * 获取文章类目下拉列表
     */
    public function getCatTree()
    {
        $catList = Db::name('article_cat')->where('state', 1)->field('cat_id as value,pid,cat_name as name')->select();
        return \Tree::getTree($catList, 'value', 'pid', 'children');
    }

}