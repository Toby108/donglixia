<?php
// +----------------------------------------------------------------------
// | Copyright (c) 2018-2018 http://www.donglixia.net All rights reserved.
// +----------------------------------------------------------------------
// | Author: 十万马 <962863675@qq.com>
// +----------------------------------------------------------------------
// | DateTime: 2018-02-09 16:17
// +----------------------------------------------------------------------

namespace app\admin_fussen\controller;

use app\admin_fussen\model\User;
use app\admin_fussen\model\UserDept;
use app\admin_fussen\model\UserLetter;
use app\admin_fussen\model\UserLetterList;
use app\admin_fussen\model\UserRole;
use think\Db;
use think\Request;

class Message extends Base
{
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->currentModel = new UserLetter();//实例化当前模型
    }

    /**
     * 列表页
     * @return mixed
     */
    public function index()
    {
        //获取人员下拉列表
        $userList = (new User())->getUserList();
        $this->assign('userList', $userList);
        //获取类型下拉列表
        $this->assign('typeList', $this->currentModel->typeList);
        return $this->fetch();
    }

    /**
     * 获取数据
     * @return mixed
     */
    public function getDataList()
    {
        //接收参数
        $param = $this->request->param();
        //获取数据
        $res = $this->currentModel->getIndexDataList($param);
        //格式化数据
        $list = $this->currentModel->formatData($res['list']);
        return ['code'=>0, 'msg'=>'获取成功', 'count'=>$res['count'], 'data'=>$list];
    }

    /**
     * 已发送页面
     * @return mixed
     */
    public function sent()
    {
        //获取类型下拉列表
        $this->assign('typeList', $this->currentModel->typeList);
        return $this->fetch();
    }

    /**
     * 获取已发送数据
     * @return mixed
     */
    public function getSentDataList()
    {
        //接收参数
        $param = $this->request->param();
        //组装查询条件
        $map = [];
        if (!empty($param['type'])) {
            $map['type'] = $param['type'];
        }
        $map['create_by'] = user_info('user_id');
        return $this->currentModel->where($map)->layTable(['type_text']);
    }

    /**
     * 编辑
     * @param int $id
     * @return mixed
     */
    public function edit($id = 0)
    {
        if (!empty($id)) {
            $data = $this->currentModel->where('id', $id)->find()->toArray();
            $receive_dept_id_arr = explode(',', $data['receive_dept_id']);
            $receive_dept_id = [];
            foreach ($receive_dept_id_arr as $k => $v) {
                $receive_dept_id[$k] = implode('/', get_parent_ids($v, 'goods_cat'));
            }
            $data['receive_dept_id'] = json_encode($receive_dept_id);
            $this->assign('data', $data);
        }
        /*获取下拉列表：部门*/
        $deptList = (new UserDept())->getDeptTree();
        $this->assign('deptTree', json_encode($deptList));

        /*获取下拉列表：角色权限*/
        $roleList = (new UserRole())->getRoleList();
        $this->assign('roleList', $roleList);

        //获取人员下拉列表
        $userList = (new User())->getUserList();
        $this->assign('userList', $userList);

        //获取类型下拉列表
        $this->assign('typeList', $this->currentModel->typeList);
        return $this->fetch();
    }

    /**
     * 更新某个字段
     */
    public function updateLetterListField()
    {
        $param = $this->request->param();
        if (empty($param['id'])) {
            $this->error('id不能为空');
        }

        $UserLetterList = new UserLetterList();
        try {
            $UserLetterList->isUpdate(true)->save($param);
        } catch (\Exception $e) {
            $this->error($UserLetterList->getError() ? $UserLetterList->getError() : $e->getMessage());
        }
        $this->success('更新成功!');
    }

    /**
     * 保存
     */
    public function save()
    {
        $param = $this->request->param();
        if (empty($param)) {
            $this->error('未获取到需要保存的数据');
        }
        if (empty($param['receive_all']) && empty($param['receive_role_id']) && empty($param['receive_dept_id']) && empty($param['receive_user_id'])) {
            $this->error('请选择发送对象！');
        }
        try{
            $user_ids = Db::name('user')->where(function ($query) use($param){
                if (!empty($param['receive_all'])) {
                    $query->whereOr('', 'exp','1=1');
                }
                if (!empty($param['receive_dept_id'])) {
                    $query->whereOr('dept_id',$param['receive_dept_id']);
                }
                if (!empty($param['receive_role_id'])) {
                    $query->whereOr('role_id',$param['receive_role_id']);
                }
                if (!empty($param['receive_user_id'])) {
                    $query->whereOr('user_id', 'in', $param['receive_user_id']);
                }
            })->column('user_id');//获取接收者id

            if (empty($user_ids)) {
                $this->error('未找到发送对象！');
            }

            $this->currentModel->save($param);//保存消息主表

            $UserLetterList = new UserLetterList();
            //若当前为编辑，则更新接收者列表
            if (!empty($param['id'])) {
                //根据letter_id，删除不在当前user_ids中的数据
                $UserLetterList->where('letter_id', $param['id'])->where('user_id', 'not in', $user_ids)->delete();
                //根据letter_id获取，已发送的user_id集合
                $user_id_exist = $UserLetterList->where('letter_id', $param['id'])->column('user_id');
                //取数组差集，剔除掉已发送的user_id
                $user_ids = array_diff($user_ids, $user_id_exist);
            }
            if (!empty($user_ids)) {
                $saveList = [];
                foreach ($user_ids as $k=>$v) {
                    $saveList[$k]['user_id'] = $v;
                    $saveList[$k]['letter_id'] = $this->currentModel->id;
                }
                $UserLetterList->saveAll($saveList);//保存发送列表
            }
        }
        catch (\Exception $e) {
            $this->error($e->getMessage());
        }
        $this->success('发送成功！', 'edit?id='.$this->currentModel->id);
    }

    /**
     * 删除
     * @param $id
     */
    public function delete($id)
    {
        try {
            Db::name('user_letter')->where('id', $id)->delete();
            Db::name('user_letter_list')->where('letter_id', $id)->delete();
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
        $this->success('删除成功!');
    }
}

