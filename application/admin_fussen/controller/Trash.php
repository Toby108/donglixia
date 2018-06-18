<?php
// +----------------------------------------------------------------------
// | Copyright (c) 2018-2018 http://www.donglixia.net All rights reserved.
// +----------------------------------------------------------------------
// | Author: 十万马 <962863675@qq.com>
// +----------------------------------------------------------------------
// | DateTime: 2018-02-09 16:17
// +----------------------------------------------------------------------

namespace app\admin_fussen\controller;

use app\admin_fussen\model\DataChangeLog;
use app\admin_fussen\parent\Controller;
use think\Db;
use think\Request;

class Trash extends Controller
{
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->currentModel = new DataChangeLog();//实例化当前模型
    }

    /**
     * 列表页
     * @return mixed
     */
    public function index()
    {
        $this->assign('table_name', $this->request->param('table_name'));
        return $this->fetch();
    }

    /**
     * 获取回收站数据
     * @return mixed
     */
    public function getDataList()
    {

        $param = $this->request->param();
        $map = [];
        if (!empty($param['create_begin']) && !empty($param['create_end'])) {
            $map['create_time'] = ['between', [strtotime($param['create_begin']), strtotime($param['create_end'])]];//删除时间
        }
        $map['state'] = 1;//有效数据
        $map['type'] = 3;//类型：删除
        $map['table_name'] = !empty($param['table_name']) ? $param['table_name'] : '';//数据表
        $data = $this->currentModel->where($map)->layTable();
        $data['data'] = $this->currentModel->formatData($data['data']);
        return $data;
    }

    /**
     * 恢复
     * @param $id
     */
    public function recover($id)
    {
        $data = Db::name('data_change_log')->where('id', $id)->field('table_name,content')->find();
        if (empty($data['content'])) {
            $this->error('资料内容为空，恢复失败!');
        }

        $data['content'] = json_decode($data['content'], true);
        Db::name($data['table_name'])->insert($data['content']);
        $this->success('恢复成功!');
    }

    /**
     * 彻底删除，state置为0
     * @param $id
     */
    public function delete($id)
    {
        $this->currentModel->whereIn('id', $id)->update(['state'=>0]);
        $this->success('删除成功!');
    }



}

