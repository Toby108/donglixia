<?php
// +----------------------------------------------------------------------
// | Copyright (c) 2018-2018 http://www.donglixia.net All rights reserved.
// +----------------------------------------------------------------------
// | Author: 十万马 <962863675@qq.com>
// +----------------------------------------------------------------------
// | DateTime: 2018-02-09 16:17
// +----------------------------------------------------------------------

namespace app\common\parent;

use think\Controller as CoreController;

class Controller extends CoreController
{
    protected $result = array();

    protected function _initialize()
    {
        \think\Hook::listen('controller_init');//添加行为标签位，触发自动执行
    }
}