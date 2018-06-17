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

class User extends Validate
{
    /*字段规则*/
    protected $rule = [
        'nick_name' => 'require|unique:user|token|checkPwd'
        ,'tel' => 'require|unique:user'
        ,'card_no' => 'unique:user'
        ,'type' => 'require'
        ,'role_id' => 'requireIf:type,159'
        ,'login_rank' => 'requireIf:type,159'
    ];

    /*返回错误信息*/
    protected $message = [
        'nick_name.require' => '帐号不能为空！'
        ,'nick_name.token' => '请勿重复提交！'
        ,'nick_name.unique' => '帐号已被使用！'
        ,'tel.require' => '手机号不能为空！'
        ,'tel.unique' => '手机号已被使用！'
        ,'card_no.unique' => '证件号已被使用！'
        ,'type.require' => '请选择用户类型！'
        ,'role_id.requireIf' => '当前用户类型为“后台管理员”，角色权限不能为空！'
        ,'login_rank.requireIf' => '当前用户类型为“后台管理员”，数据权限不能为空！'
    ];

    protected $scene = [
        'updateField' => ['nick_name'=>'unique', 'tel'=>'unique:user','card_no'=>'unique:user']
    ];

    /**
     * 校验密码
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     */
    protected function checkPwd($value, $rule, $data)
    {
        //添加用户时，新密码必填
        if (empty($data['uid']) && empty($data['password'])) {
            return '密码不能为空';
        }

        //编辑用户时，验证确认密码
        if (!empty($data['password'])) {
            if (empty($data['confirm']) || ($data['password'] != $data['confirm'])) {
                return '两次输入的密码不一致';
            }

            if (!password_strength($data['password'])) {
                return '密码太简单，请重新修改';
            }
        }
        return true;
    }

}