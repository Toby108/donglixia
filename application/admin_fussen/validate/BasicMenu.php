<?php
// +----------------------------------------------------------------------
// | Copyright (c) 2018-2018 http://www.donglixia.net All rights reserved.
// +----------------------------------------------------------------------
// | Author: 十万马 <962863675@qq.com>
// +----------------------------------------------------------------------
// | DateTime: 2018-02-09 16:17
// +----------------------------------------------------------------------

namespace app\admin_fussen\validate;

use think\Validate;

class BasicMenu extends Validate
{
    /*字段规则*/
    protected $rule = [
        'name' => 'require|token'
    ];

    /*返回错误信息*/
    protected $message = [
        "name.require" => '菜单名称不能为空！'
        ,"name.token" => '请勿重复提交！'
    ];

    protected $scene = [

    ];
}