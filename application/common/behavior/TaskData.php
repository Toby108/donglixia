<?php
// +----------------------------------------------------------------------
// | Copyright (c) 2018-2018 http://www.donglixia.net All rights reserved.
// +----------------------------------------------------------------------
// | Author: 十万马 <962863675@qq.com>
// +----------------------------------------------------------------------
// | DateTime: 2018-02-09 16:17
// +----------------------------------------------------------------------

namespace app\common\behavior;

use think\Request;

class TaskData
{
    /**
     * 定时任务
     */
    public function run()
    {
        $request = Request::instance();
        $url = $request->domain() . '/admin_fussen/daily_task/all.html';
        sock_open($url);//定时任务非阻塞模式
//        header('Location: '.$url);//测试
    }

    /**
     * 定时任务
     */
    public function test()
    {
        die('1');
//        header('Location: '.$url);//测试
    }

}

