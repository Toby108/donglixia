<?php
// +----------------------------------------------------------------------
// | Copyright (c) 2018-2018 http://www.donglixia.net All rights reserved.
// +----------------------------------------------------------------------
// | Author: 十万马 <962863675@qq.com>
// +----------------------------------------------------------------------
// | DateTime: 2018-02-09 16:17
// +----------------------------------------------------------------------

namespace app\admin_fussen\controller;

use app\admin_fussen\model\UserRole;
use think\Db;
use think\Request;

class Role extends Base
{
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->currentModel = new UserRole();//实例化当前模型
    }

    /**
     * 列表页
     * @return mixed
     */
    public function index()
    {
        return $this->fetch();
    }

    /**
     * 列表页，获取数据
     * @return mixed
     */
    public function getDataList()
    {
        $param = $this->request->param();
        if (!empty($param['role_name'])) {
            $map['role_name'] = ['like', '%'.$param['role_name'].'%'];//角色名称
        }
        if (empty($map)) {
            $map[] = ['exp', '1=1'];
        }

        return $this->currentModel->where($map)->order('sort_num')->layTable();
    }

    /**
     * 编辑
     * @return mixed
     */
    public function edit()
    {
        $param = $this->request->param();

        if (!empty($param['role_id'])) {
            $data = $this->currentModel->where('role_id', $param['role_id'])->field('role_id,role_name,describe,auth')->find()->toArray();
            $this->assign('data', $data);
        }

        return $this->fetch();
    }

    /**
     * 获取菜单列表数据
     * @param $role_id
     * @return array
     */
    public function getMenuList($role_id)
    {
        $list = Db::name('basic_menu')->field('menu_id,pid,menu_name,sort_num,url,description')->order('sort_num asc')->select();
        $auth = Db::name('user_role')->where('role_id', $role_id)->value('auth');
        $auth_arr = explode(',', $auth);
        $list = \Tree::get_Table_tree($list, 'menu_name', 'menu_id');
        foreach ($list as $k=>$v) {
            $list[$k]['pid_text'] = !empty($v['pid']) ? Db::name('basic_menu')->where('menu_id', $v['pid'])->value('menu_name') : '顶级';
            $list[$k]['LAY_CHECKED'] = in_array($v['menu_id'], $auth_arr) || $v['menu_id'] == 1 ? true : false;
            unset($list[$k]['child']);
        }
        return ['code'=>0, 'msg'=>'获取成功', 'count'=>0, 'data'=>$list];
    }

    /**
     * 保存数据
     */
    public function save()
    {
        $param = $this->request->param();

        //验证数据
        $result = $this->validate($param, 'UserRole');
        if ($result !== true) {
            $this->error($result, null, ['token'=>$this->request->token()]);
        }

        //保存数据
        $row = $this->currentModel->save($param);

        //返回错误
        if ($row === false) {
            $this->error($this->currentModel->getError(), null, ['token'=>$this->request->token()]);
        }

        $this->success('保存成功！', 'edit?role_id='.$this->currentModel->role_id);
    }

    /**
     * 编辑字段
     */
    public function updateField()
    {
        $param = $this->request->param();
        if (empty($param['role_id'])) {
            $this->error('角色id不能为空');
        }

        //验证数据
        $result = $this->validate($param, 'UserRole.updateField');
        if ($result !== true) {
            $this->error($result);
        }

        //保存数据
        $row = $this->currentModel->isUpdate(true)->save($param);

        //返回错误
        if ($row === false) {
            $this->error($this->currentModel->getError());
        }

        $this->success('保存成功！', 'index');
    }

}

