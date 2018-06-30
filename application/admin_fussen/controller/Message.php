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
        return $this->fetch();
    }

    /**
     * 获取数据
     * @return mixed
     */
    public function getDataList()
    {

        $param = $this->request->param();
        $map = [];
        if (!empty($param['type'])) {
            $map['type'] = $param['type'];//通知类型：1公告，2系统消息 ，3产品上新，4文章发布
        }
        if (empty($map)) {
            $map[] = ['exp', '1=1'];
        }
        return $this->currentModel->where($map)->order('id desc')->layTable();
    }


}

