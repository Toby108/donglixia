<?php
// +----------------------------------------------------------------------
// | Copyright (c) 2018-2018 http://www.donglixia.net All rights reserved.
// +----------------------------------------------------------------------
// | Author: 十万马 <962863675@qq.com>
// +----------------------------------------------------------------------
// | DateTime: 2018-02-09 16:17
// +----------------------------------------------------------------------

namespace app\admin_fussen\controller;

use app\admin_fussen\parent\Controller;
use app\admin_fussen\model\BasicInfo as BasicInfoModel;
use think\Request;

class BasicInfo extends Controller
{
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->currentModel = new BasicInfoModel();//实例化当前模型
    }

    public function index()
    {
        return $this->fetch();
    }

    /**
     * 获取数据列表
     */
    public function getDataList()
    {
        $param = $this->request->param();

        if (!empty($param['pid'])) {
            $ids = get_child_ids($param['pid'], 'basic_info', false);
            $map['basic_id'] = ['in', $ids];
        }

        if (!empty($param['name'])) {
            $map['name'] = ['like','%'.$param['name'].'%'];
        }
        if (empty($map)) {
            $map[] = ['exp', '1=1'];
        }

        return $this->currentModel->where($map)
            ->field('basic_id,pid as pid_text,code,name,sort_num,description,status')
            ->order('pid,sort_num asc')->layTable();
    }

    /**
     * 编辑
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function edit()
    {
        $param = $this->request->param();

        if (!empty($param['basic_id'])) {
            $data = $this->currentModel->where('basic_id', $param['basic_id'])->find();
            $pid_arr = get_parent_ids($data['pid'], 'basic_info');
            $data['pid_multi'] = json_encode($pid_arr);
            $this->assign('data', $data);
        }
        return $this->fetch();
    }

    /**
     * 保存
     */
    public function save()
    {
        $param = $this->request->param();
        if (empty($param)) {
            $this->error('没有需要保存的数据！');
        }

        $param['pid'] = !empty($param['pid']) ? $param['pid'] : 0 ;
        $cat_code = $this->currentModel->where('basic_id', $param['pid'])->value('code');
        $param['cat_code'] = !empty($cat_code) ? $cat_code : 'top';

        if (empty($param['code'])) {
            $res = $this->createCode($param['pid']);
            if ($res['code'] != 1) {
                $this->error($res['msg'], null, ['token'=>$this->request->token()]);
            }
            $param['code'] = $res['data'];
        }

        if (empty($param['sort_num'])) {
            $max_sort = $this->currentModel->where('pid', $param['pid'])->max('sort_num');
            $param['sort_num'] = $max_sort+1;
        }

        //验证数据
        $result = $this->validate($param, 'BasicInfo');
        if ($result !== true) {
            $this->error($result, null, ['token'=>$this->request->token()]);
        }

        $res = $this->currentModel->save($param);
        if ($res === false) {
            $this->error($this->currentModel->getError(), null, ['token'=>$this->request->token()]);
        }
        $this->success('保存成功！', 'edit?basic_id='.$this->currentModel->basic_id);
    }

    /**
     * 编辑字段
     */
    public function updateField()
    {
        $param = $this->request->param();
        if (empty($param['basic_id'])) {
            $this->error('资料id不能为空');
        }

        //保存数据
        $row = $this->currentModel->isUpdate(true)->save($param);

        //返回错误
        if ($row === false) {
            $this->error($this->currentModel->getError());
        }

        $this->success('保存成功！', 'index');
    }

    /**
     * 更改排序
     */
    public function changeSort()
    {
        $param = $this->request->param();
        if (empty($param['basic_id']) || empty($param['type'])) {
            $this->error('资料id 或 排序类型不能为空');
        }

        //格式化，获取重新排序的数据
        $list = reset_sort($param['basic_id'], 'basic_info', $param['type']);

        //保存数据
        $res = $this->currentModel->saveAll($list);
        if ($res === false) {
            $this->error($this->currentModel->getError());
        }

        $this->success('操作成功');
    }

    /**
     * 删除
     * @param $id
     */
    public function delete($id)
    {
        try{
            $this->currentModel->whereIn('pid', $id)->delete();//删除子类资料
            $this->currentModel->whereIn('basic_id', $id)->delete();//删除当前资料
        } catch (\Exception $e) {
            $msg = !empty($this->currentModel->getError()) ? $this->currentModel->getError() : $e->getMessage();
            $this->error($msg);
        }
        $this->success('删除成功!');
    }

    /**
     * 根据pid 获取下拉列表，级联选择
     * @return array
     */
    public function getBasicList()
    {
        $param = $this->request->param();
        $pid = !empty($param['id']) ? $param['id'] : 0;
        $map['pid'] = $pid;
        $map['status'] = 1;//状态：0禁用，1启用

        if (!empty($param['basic_id'])) {
            $map['basic_id'] = ['<>', $param['basic_id']];
        }

        $data =  $this->currentModel->where($map)->field('basic_id as id,name,cat_code')->order('sort_num')->select();
        $this->success('获取成功', null, $data);
    }

    /**
     * 获取基础资料详情
     * @param $id
     * @return array|false|\PDOStatement|string|\think\Model
     */
    public function getBasicInfo($id)
    {
        return $this->currentModel->where('basic_id', $id)->field('basic_id,pid,cat_code,code,name')->find();
    }

    /**
     * 生成资料代号
     * @param $pid
     * @return array
     */
    protected function createCode($pid)
    {
        if (empty($pid)) {
            return ['code'=>0, 'msg'=>'当前新资料代号无规律可循，请手动输入'];
        }

        $max_code = $this->currentModel->where('pid', $pid)->order('code desc')->value('code');
        $cat_code = $this->currentModel->where('basic_id', $pid)->value('code');

        //若上级代号非AA类型
        if (!preg_match('/^[A-Z]{2}/',$cat_code)) {
            //当前没有同级别代号，则初始化为AA
            if (empty($max_code)) {
                return ['code'=>1, 'msg'=>'操作成功', 'data'=>'AA'];
            }

            if (!preg_match('/^[A-Z]{2}/',$max_code)) {
                return ['code'=>0, 'msg'=>'当前新资料代号无规律可循，请手动输入'];
            }
        }

        if (strlen($max_code) == 2) {
            $res = serials_number($max_code, 2 , '');
        } else {
            $res = !empty($max_code) ? ++$max_code : $cat_code.'0001';
        }
        return ['code'=>1, 'msg'=>'操作成功', 'data'=>$res];
    }
}

