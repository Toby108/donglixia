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
     * @param int $times 每天执行的次数上限，0表示无限次
     */
    public function all($times = 10)
    {
        $filename = STATIC_PATH. '/logs/period/' . date("Ymd", time()) . '.log';
        $logs_period = include $filename;
        if (!empty($times) && count($logs_period) <= $times) {
            $this->test();//测试
            $this->articleGoodsPublic();//文章、产品定时发布
            $this->deleteTempFile();//删除三天前的临时图片
            log_write('period','执行成功！');
        }
    }

    /**
     * 测试
     */
    public function test()
    {
        Db::name('user_account_log')->insert(['user_id'=>3, 'remark'=>'测试定时任务']);
    }

    /**
     * 文章、产品定时发布
     */
    public function articleGoodsPublic()
    {
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

