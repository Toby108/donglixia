<?php
// +----------------------------------------------------------------------
// | Copyright (c) 2018-2018 http://www.donglixia.net All rights reserved.
// +----------------------------------------------------------------------
// | Author: 十万马 <962863675@qq.com>
// +----------------------------------------------------------------------
// | DateTime: 2018-02-09 16:17
// +----------------------------------------------------------------------

namespace app\admin_fussen\controller;

use think\Controller;
use think\Db;

class Period extends Controller
{
    /**
     * 执行全部
     */
    public function all()
    {
        $this->articleGoodsPublic();//文章、产品定时发布
        $this->deleteTempFile();//删除三天前的临时图片
    }

    /**
     * 文章、产品定时发布
     */
    public function articleGoodsPublic()
    {
        log_write('info','测试');

        //文章定时发布
        $article = Db::name('article')->where('state', 0)->where('public_time', '<=', time())->column('art_id');
        foreach ($article as $k => $v) {
            Db::name('article')->where('art_id', $v)->update(['state' => 1]);
        }
        //产品定时发布
        $goods = Db::name('goods')->where('state', 0)->where('public_time', '<=', time())->column('goods_id');
        foreach ($goods as $k => $v) {
            Db::name('goods')->where('goods_id', $v)->update(['state' => 1]);
        }
    }

    /**
     * 删除临时文件
     */
    public function deleteTempFile()
    {
        delete_file_by_time(STATIC_PATH.'/img/temp', 72);//删除三天前的临时图片
        delete_file_by_time(STATIC_PATH.'/img/temp', 168);//删除七天前的static/logs日志文件
    }


}

