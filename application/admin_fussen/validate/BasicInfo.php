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

class BasicInfo extends Validate
{
    /*字段规则*/
    protected $rule = [
        'basic_name' => 'require|token'
        ,'cat_code' => 'require'
        ,'basic_code' => 'require'
    ];

    /*返回错误信息*/
    protected $message = [
        "basic_name.require" => '资料名称不能为空！'
        ,"basic_name.token" => '请勿重复提交！'
        ,"cat_code.require" => '上级代号不能为空！'
        ,"basic_code.require" => '代号不能为空！'
    ];

    protected $scene = [

    ];
}