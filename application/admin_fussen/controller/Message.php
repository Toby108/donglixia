<?php
// +----------------------------------------------------------------------
// | Copyright (c) 2018-2018 http://www.donglixia.net All rights reserved.
// +----------------------------------------------------------------------
// | Author: 十万马 <962863675@qq.com>
// +----------------------------------------------------------------------
// | DateTime: 2018-02-09 16:17
// +----------------------------------------------------------------------

namespace app\admin_fussen\controller;

use app\admin_fussen\model\UserLetter;
use app\admin_fussen\model\UserLetterList;
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
        $userList = $this->currentModel->getUserList();
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
     * 更新某个字段
     */
    public function updateField()
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

}

