<?php
// +----------------------------------------------------------------------
// | Copyright (c) 2018-2018 http://www.donglixia.net All rights reserved.
// +----------------------------------------------------------------------
// | Author: 十万马 <962863675@qq.com>
// +----------------------------------------------------------------------
// | DateTime: 2018-02-09 16:17
// +----------------------------------------------------------------------

namespace app\index\controller;

use think\Controller;
use think\Db;

class DailyTask extends Controller
{
    /**
     * 执行全部
     * @param int $time 默认每隔3600秒执行一次（一个小时）
     * @return bool
     */
    public function all($time = 10)
    {
        try {
            $file = STATIC_PATH. '/logs/daily_task/' . date("Ymd", time()) . '.log';
            $logs = log_read($file);
            $time_log = end($logs)['time'];
            if (empty($logs) || (time() - strtotime($time_log) >= $time)) {
                $this->test();//测试
                $this->articleGoodsPublic();//文章、产品定时发布
                $this->deleteTempFile();//删除临时文件
                log_write('daily_task','执行成功！');
            }
        } catch (\Exception $e) {
            save_error_log($e->getMessage().' ['.$e->getFile().':'.$e->getLine().']');
            die($e->getMessage());
        }
        die('success');
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

