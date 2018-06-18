<?php
// +----------------------------------------------------------------------
// | Copyright (c) 2018-2018 http://www.donglixia.net All rights reserved.
// +----------------------------------------------------------------------
// | Author: 十万马 <962863675@qq.com>
// +----------------------------------------------------------------------
// | DateTime: 2018-02-09 16:17
// +----------------------------------------------------------------------

namespace app\common\parent;

use think\db\Query as CoreQuery;

class Query extends CoreQuery
{
    public function layTable($append = [], $hidden = [], $visible = [])
    {
        $options =$this->getOptions();//获取当前的查询参数

        $request = \think\Request::instance();
        $param = $request->param();//获取前端传过来的参数

        $count = $this->count();//得到记录数量

        /*查找数据*/
        if (!empty($param['limit'])) {
            $param['limit'] = $param['limit'] < 200 ? $param['limit'] : 200;
            $list = $this->options($options)->page($param['page'], $param['limit'])->select();
        } else {
            $list = $this->options($options)->select();
        }

        /*追加字段，隐藏字段，显示字段*/
        if (!empty($list)) {
            $list = collection($list)->append($append)->hidden($hidden)->visible($visible)->toArray();
        }

        return ['code' => 0, 'msg'=>"", 'count' => $count, 'data' => $list];
    }

    public function select2()
    {
        $request = \think\Request::instance();
        $tmpOption = $this->options;
        //得到总记录数
        $total = $Total = $this->count();
        $this->options = $tmpOption;
        //得到参数
        $page = $request->param('page');
        $row = $request->param('row');
        $list = $this->page($page,$row)->select();
        return ['total'=>$total,'items'=>$list];

    }

}