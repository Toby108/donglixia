<?php
// +----------------------------------------------------------------------
// | Copyright (c) 2018-2018 http://www.donglixia.net All rights reserved.
// +----------------------------------------------------------------------
// | Author: 十万马 <962863675@qq.com>
// +----------------------------------------------------------------------
// | DateTime: 2018-02-09 16:17
// +----------------------------------------------------------------------

namespace app\index\controller;

use think\Db;

class DailyTask
{
    /**
     * 执行全部
     * @param int $time 默认每隔600秒执行一次（十分钟）
     * @return bool
     */
    public function all($time = 600)
    {
        try {
            $create_time = Db::name('task_log')->where('task_name', 'DailyTask')->order('id desc')->value('create_time');
            if (empty($create_time) || (time() - strtotime($create_time) >= $time)) {
                $this->articleGoodsPublic();//文章、产品定时发布
                $this->deleteTempFile();//删除临时文件
                save_task_log('每日任务执行成功！', 1, 'DailyTask');
            }
        } catch (\Exception $e) {
            save_task_log('每日任务执行失败！', 0, 'DailyTask');
            save_error_log($e->getMessage().' ['.$e->getFile().':'.$e->getLine().']');
            die($e->getMessage());
        }
        die('success');
    }

    /**
     * 文章、产品定时发布
     */
    private function articleGoodsPublic()
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
    private function deleteTempFile()
    {
        delete_file_by_time(STATIC_PATH.'/img/temp', 72);//删除三天前的临时图片
        delete_file_by_time(STATIC_PATH.'/logs', 168);//删除七天前的static/logs日志文件
    }


}

