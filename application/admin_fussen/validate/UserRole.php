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

class UserRole extends Validate
{
    /*字段规则*/
    protected $rule = [
        'role_name' => 'require|token|unique:user_role'
        ,'auth' => 'require'
    ];

    /*返回错误信息*/
    protected $message = [
        "role_name.require" => '角色名称不能为空！'
        ,"role_name.token" => '请勿重复提交！'
        ,"role_name.unique" => '角色名称已被使用，不能重复！'
        ,"auth.require" => '至少选择一项权限！'
    ];

    protected $scene = [
        'updateField' => ['role_name'=>'unique:user_role']
    ];

}