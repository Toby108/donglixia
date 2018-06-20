<?php
// +----------------------------------------------------------------------
// | Copyright (c) 2018-2018 http://www.donglixia.net All rights reserved.
// +----------------------------------------------------------------------
// | Author: 十万马 <962863675@qq.com>
// +----------------------------------------------------------------------
// | DateTime: 2018-02-09 16:17
// +----------------------------------------------------------------------

namespace app\admin_fussen\controller;

use app\admin_fussen\model\GoodsCat as GoodsCatModel;
use app\admin_fussen\parent\Controller;
use think\Request;
use think\Cookie;
use think\Session;

class GoodsCat extends Controller
{
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->currentModel = new GoodsCatModel();//实例化当前模型
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
        $result = $this->validate($param, 'GoodsCat');
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
     * 获取类目列表
     */
    public function getGoodsCatList()
    {
        $catList = $this->currentModel->field('cat_id,pid,cat_name')->select();
        return \Tree::get_Table_tree($catList, 'cat_name', 'cat_id');
    }

    /**
     * 根据pid 获取下拉列表，级联选择
     */
    public function getListToLinkSelect()
    {
        $param = $this->request->param();
        $pid = !empty($param['id']) ? $param['id'] : 0;
        $map['pid'] = $pid;
        $map['state'] = 1;//状态：0禁用，1启用

        if (!empty($param['cat_id'])) {
            $map['cat_id'] = ['<>', $param['cat_id']];
        }

        $data =  Db::name('goods_cat')->where($map)->field('cat_id as id,cat_name as name')->order('sort_num')->select();
        $this->success('获取成功', null, $data);
    }
}

