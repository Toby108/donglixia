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
     * 列表页
     * @return mixed
     */
    public function index()
    {
        return '1';
    }

    /**
     * 列表页
     * @return mixed
     */
    public function test()
    {
        Db::name('user_account_log')->insert(['user_id'=>3, 'remark'=>'测试定时任务']);
        return '2';
    }


}

