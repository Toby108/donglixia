<?php
// +----------------------------------------------------------------------
// | Copyright (c) 2018-2018 http://www.donglixia.net All rights reserved.
// +----------------------------------------------------------------------
// | Author: 十万马 <962863675@qq.com>
// +----------------------------------------------------------------------
// | DateTime: 2018-02-09 16:17
// +----------------------------------------------------------------------

namespace app\admin_fussen\controller;

use app\admin_fussen\model\ArticleCat as ArticleCatModel;
use app\admin_fussen\parent\Controller;
use think\Request;
use think\Session;

class ArticleCat extends Controller
{
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->currentModel = new ArticleCatModel();//实例化当前模型
    }

    public function index()
    {
        $catList = $this->currentModel->field('cat_id,pid,cat_name')->select();
        $catList = \Tree::get_Table_tree($catList, 'cat_name', 'cat_id');
        $this->assign('catList', $catList);
        return $this->fetch();
    }

    /**
     * 获取数据列表
     */
    public function getDataList()
    {
        $param = $this->request->param();

        if (!empty($param['cat_name'])) {
            $map['cat_name'] = ['like', '%'.$param['cat_name'].'%'];
        }

        $map['language'] = !empty(Session::get('config.language')) ? Session::get('config.language') : 1;
        $count = $this->currentModel->where($map)->count();
        $list = $this->currentModel->where($map)
            ->page($param['page'], $param['limit'])
            ->field('cat_id,pid,cat_name,cat_name as cat_name_text')
            ->select();
        $list = \Tree::get_Table_tree($list, 'cat_name_text', 'cat_id');
        return ['code'=>0, 'msg'=>'', 'count'=>$count, 'data'=>$list];
    }

    /**
     * 保存数据
     */
    public function save()
    {
        $param = $this->request->param();//获取请求数据

        //验证数据
        $result = $this->validate($param, 'ArticleCat');
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
     * 删除
     * @param $id
     */
    public function delete($id)
    {
        try{
            $this->currentModel->whereIn('pid', $id)->delete();//删除子类资料
            $this->currentModel->whereIn('cat_id', $id)->delete();//删除当前资料
        } catch (\Exception $e) {
            $msg = !empty($this->currentModel->getError()) ? $this->currentModel->getError() : $e->getMessage();
            $this->error($msg);
        }
        $this->success('删除成功!');
    }

    /**
     * 获取栏目列表
     */
    public function getCatList()
    {
        $catList = $this->currentModel->field('cat_id,pid,cat_name')->select();
        return \Tree::get_Table_tree($catList, 'cat_name', 'cat_id');
    }
}

