<?php
// +----------------------------------------------------------------------
// | Copyright (c) 2018-2018 http://www.donglixia.net All rights reserved.
// +----------------------------------------------------------------------
// | Author: 十万马 <962863675@qq.com>
// +----------------------------------------------------------------------
// | DateTime: 2018-02-09 16:17
// +----------------------------------------------------------------------

namespace app\admin_fussen\controller;

use app\admin_fussen\model\UserDept;
use app\admin_fussen\parent\Controller;
use think\Request;

class Dept extends Controller
{
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->currentModel = new UserDept();//实例化当前模型
    }

    public function index()
    {
        $deptList = $this->currentModel->field('dept_id,pid,dept_name')->order('sort_num')->select();
        $deptList = \Tree::get_Table_tree($deptList, 'dept_name', 'dept_id');
        $this->assign('deptList', $deptList);
        return $this->fetch();
    }

    /**
     * 获取数据列表
     */
    public function getDataList()
    {
        $param = $this->request->param();

        if (!empty($param['dept_name'])) {
            $map['dept_name'] = ['like', '%'.$param['dept_name'].'%'];
        }

        if (empty($map)) {
            $map[] = ['exp', '1=1'];
        }

        $count = $this->currentModel->where($map)->count();
        $list = $this->currentModel->where($map)
            ->page($param['page'], $param['limit'])
            ->order('sort_num')
            ->field('dept_id,pid,dept_name,dept_name as dept_name_text')
            ->select();
        $list = \Tree::get_Table_tree($list, 'dept_name_text', 'dept_id');
        return ['code'=>0, 'msg'=>'', 'count'=>$count, 'data'=>$list];
    }

    /**
     * 保存数据
     */
    public function save()
    {
        $param = $this->request->param();//获取请求数据

        //验证数据
        $result = $this->validate($param, 'UserDept');
        $token = $this->request->token();//验证数据后，重新生成表单令牌
        if ($result !== true) {
            $this->error($result, null, ['token'=>$token]);
        }

        //保存数据
        $row = $this->currentModel->save($param);

        //返回错误
        if ($row === false) {
            $this->error($this->currentModel->getError(), null, ['token'=>$this->request->token()]);
        }

        $this->success('保存成功！', 'index', ['token'=>$token]);
    }

    /**
     * 获取部门列表
     */
    public function getDeptList()
    {
        $deptList = $this->currentModel->field('dept_id,pid,dept_name')->select();
        return \Tree::get_Table_tree($deptList, 'dept_name', 'dept_id');
    }
}

