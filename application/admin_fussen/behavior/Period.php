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

class Period
{
    /**
     * 定时任务
     */
    public function run()
    {
        //定时任务非阻塞模式，admin_fussen模块
        $request = Request::instance();
        $url = $request->domain() . '/admin_fussen/Period/all.html';
        sock_open($url);

    }



}

