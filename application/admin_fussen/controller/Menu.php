<?php
// +----------------------------------------------------------------------
// | Copyright (c) 2018-2018 http://www.donglixia.net All rights reserved.
// +----------------------------------------------------------------------
// | Author: 十万马 <962863675@qq.com>
// +----------------------------------------------------------------------
// | DateTime: 2018-02-09 16:17
// +----------------------------------------------------------------------

namespace app\admin_fussen\controller;

use app\admin_fussen\model\BasicMenu;
use app\admin_fussen\parent\Controller;
use think\Request;

class Menu extends Controller
{
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->currentModel = new BasicMenu();//实例化当前模型
    }

    /**
     * 菜单列表页面
     * @return mixed
     */
    public function index()
    {
        return $this->fetch();
    }

    /**
     * 菜单列表页面 获取数据
     * @throws \think\exception\DbException
     * @return array
     */
    public function getDataList()
    {
        $param = $this->request->param();
        if (!empty($param['pid'])) {
            $map['pid'] = $param['pid'];
        }
        if (!empty($param['menu_name'])) {
            $map['menu_name'] = ['like', '%'.$param['menu_name'].'%'];
        }
        if (!empty($param['url'])) {
            $map['url'] = ['like', '%'.$param['url'].'%'];
        }
        if (empty($map)) {
            $map[] = ['exp', '1=1'];
        }
        $count = $this->currentModel->where($map)->count();
        $list = $this->currentModel->where($map)
            ->field('menu_id,pid,pid as pid_text,menu_name,sort_num,url,description,display as display_text, 
            open_type as open_type_text,menu_name as menu_name_text,is_extend as is_extend_text')
            ->order('sort_num asc')
            ->select();
        $list = \Tree::get_Table_tree($list, 'menu_name_text', 'menu_id');

        foreach ($list as $key=>$val) {
            unset($list[$key]['child']);
        }

        $data = array_slice($list, ($param['page'] - 1) * $param['limit'], $param['limit']);
        return ['code'=>0, 'msg'=>'', 'count'=>$count, 'data'=>$data];
    }

    /**
     * 编辑
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function edit()
    {
        $param = $this->request->param();

        if (!empty($param['menu_id'])) {
            $data = $this->currentModel->where('menu_id', $param['menu_id'])->find();
            $pid_arr = get_parent_ids($data['pid'], 'basic_menu');
            $data['pid_multi'] = json_encode($pid_arr);
            $this->assign('data', $data);
        }

        return $this->fetch();
    }


    public function save()
    {
        $param = $this->request->param();

        if (empty($param)) {
            $this->error('没有需要保存的数据！');
        }

        if (empty($param['sort_num'])) {
            $max_sort = $this->currentModel->where('pid', $param['pid'])->max('sort_num');
            $param['sort_num'] = $max_sort+1;
        }

        //验证数据
        $result = $this->validate($param, 'BasicMenu');
        if ($result !== true) {
            $this->error($result, null, ['token'=>$this->request->token()]);
        }

        $res = $this->currentModel->save($param);
        if ($res === false) {
            $this->error($this->currentModel->getError(), null, ['token'=>$this->request->token()]);
        }
        $this->success('保存成功！', 'edit?menu_id='.$this->currentModel->menu_id);
    }

    /**
     * 根据id，获取下一级菜单
     * @param $id
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function getChildInfo($id)
    {
        return $this->currentModel->where('pid', $id)->field('menu_id,pid,menu_name,url')->select();
    }

    /**
     * 更改排序
     */
    public function changeSort()
    {
        $param = $this->request->param();
        if (empty($param['menu_id']) || empty($param['type'])) {
            $this->error('菜单id 或 排序类型不能为空');
        }

        //格式化，获取重新排序的数据
        $menuList = reset_sort($param['menu_id'], 'basic_menu', $param['type']);

        //保存数据
        $res = $this->currentModel->saveAll($menuList);
        if ($res === false) {
            $this->error($this->currentModel->getError());
        }

        $this->success('操作成功');
    }

    /**
     * 根据pid 获取下拉列表，级联选择
     * @return array
     */
    public function getMenuList()
    {
        $param = $this->request->param();
        $pid = !empty($param['id']) ? $param['id'] : 0;
        $map['pid'] = $pid;
        $map['display'] = 1;//显示状态：1显示在左侧菜单；2不显示，只作为权限判断

        if (!empty($param['menu_id'])) {
            $map['menu_id'] = ['<>', $param['menu_id']];
        }

        $data =  $this->currentModel->where($map)->field('menu_id as id,menu_name as name')->order('sort_num')->select();
        $this->success('获取成功', null, $data);
    }
}

