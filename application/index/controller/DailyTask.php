<?php
// +----------------------------------------------------------------------
// | Copyright (c) 2018-2018 http://www.donglixia.net All rights reserved.
// +----------------------------------------------------------------------
// | Author: 十万马 <962863675@qq.com>
// +----------------------------------------------------------------------
// | DateTime: 2018-02-09 16:17
// +----------------------------------------------------------------------

namespace app\index\controller;

use think\Cookie;
use think\Db;

class DailyTask
{
    /**
     * 执行全部
     * @param int $time 默认每隔600秒执行一次
     * @return bool
     */
    public function all($time = 600)
    {
        try {
            //先检查cookie是否已过期，速度快。
            if (!empty(Cookie::get('task_runtime'))) {
                return '执行时间没到';
            }
            //获取任务表最后一次执行的时间，判断是否达到规定的时间间隔
            $create_time = Db::name('task_log')->where('task_name', 'DailyTask')->order('id desc')->value('create_time');
            if (!empty($create_time) && time() - strtotime($create_time) < $time) {
                return '执行时间没到';
            }
            Cookie::set('task_runtime', time(), $time);//根据规定的时间间隔，设置cookie有效期
            $this->articleGoodsPublic();//文章、产品定时发布

            //设置执行时间段
            $data = date('Hi');//当前时间：时分格式
            if ($data <= '0700') {
                //0点到7点
                $this->deleteTempFile();
                $this->checkAuthData();
                save_task_log('0点到7点，任务执行成功！', 1, 'DailyTask');
            } elseif ($data <= '1200') {
                //7点到12点
                $this->checkAuthData();
                save_task_log('7点到12点，任务执行成功！', 1, 'DailyTask');
            } elseif ($data <= '1800') {
                //12点到18点
                save_task_log('12点到18点，任务执行成功！', 1, 'DailyTask');
            } elseif ($data <= '2400') {
                //18点到14点
                $this->checkAuthData();
                save_task_log('18点到14点，任务执行成功！', 1, 'DailyTask');
            }

            save_task_log('任务执行成功！', 1, 'DailyTask');
        } catch (\Exception $e) {
            save_task_log('任务执行失败！', 0, 'DailyTask');
            save_error_log($e->getMessage().' ['.$e->getFile().':'.$e->getLine().']');
            return $e->getMessage();
        }
        return 'success';
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

    /**
     * 数据校对：角色权限需包含“首页”、“消息列表”
     */
    private function checkAuthData()
    {
        $res = Db::name('user_role')
            ->where('', 'exp', 'find_in_set(1,auth)=0')//不包含首页
            ->whereOr('', 'exp', 'find_in_set(79,auth)=0')//不包含消息列表
            ->whereOr('', 'exp', 'find_in_set(80,auth)=0')
            ->count();
        if ($res > 0) {
            $sql = Db::name('user_role')->getLastSql();
            send_message(['title' => '角色权限默认值不正确', 'content' => $sql, 'role_id' => 1]);
        }
    }

}

