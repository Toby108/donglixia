<?php
// +----------------------------------------------------------------------
// | Copyright (c) 2018-2018 http://www.donglixia.net All rights reserved.
// +----------------------------------------------------------------------
// | Author: 十万马 <962863675@qq.com>
// +----------------------------------------------------------------------
// | DateTime: 2018-02-09 16:17
// +----------------------------------------------------------------------

namespace app\admin_fussen\model;

use app\common\model\Base;
use think\Db;

class UserLetter extends Base
{
    //类型列表
    public $typeList = ['1'=>'系统消息', '2'=>'系统公告', '3'=>'文章发布', '4'=>'产品上新'];

    /**
     * 获取“类型”名称
     * @param $value
     * @param $data
     * @return mixed|string
     */
    public function getTypeTextAttr($value, $data)
    {
        $item = $this->typeList;
        return !empty($data['type']) ? $item[$data['type']] : '';
    }

    /**
     * 格式化数据
     * @param array $data
     * @return array
     */
    public function formatData($data = [])
    {
        $data = !empty($data) && is_array($data) ? $data : [];
        foreach ($data as $k=>$v) {
            $data[$k]['create_by'] = $v['create_by'];
            $data[$k]['create_by_name'] = !empty($v['create_by']) ? Db::name('user')->where('user_id', $v['create_by'])->value('nick_name') : '';
            $data[$k]['create_time'] = !empty($v['create_time']) ? date('Y-m-d H:i',$v['create_time']) : '';
            $data[$k]['type_text'] = !empty($v['type']) ? $this->typeList[$v['type']] : '';
        }
        return $data;
    }

    /**
     * 获取列表页数据
     * @param $param
     * @return array
     */
    public function getIndexDataList($param)
    {
        $map = [];
        if (isset($param['is_read']) && $param['is_read'] !== "") {
            $map['li.is_read'] = $param['is_read'];//是否已读
        }
        if (!empty($param['create_by'])) {
            $map['le.create_by'] = $param['create_by'];//发送人id
        }
        if (!empty($param['type'])) {
            $map['le.type'] = $param['type'];//通知类型：1公告，2系统消息 ，3新发布
        }

        $page = !empty($param['page']) ? $param['page'] : 1;//页码
        $limit = !empty($param['limit']) && $param['limit']<=200 ? $param['limit'] : 1;//每页显示数量，最大200条
        $count = $this->getIndexDataSql($map)->count();
        $list = $this->getIndexDataSql($map)
            ->field('li.id as list_id,li.letter_id,li.user_id,li.is_read,le.title,le.content,le.url,le.type,le.device,le.create_by,le.create_time')
            ->page($page, $limit)
            ->order('li.is_read asc,li.id desc')
            ->select();
        return ['count'=>$count, 'list'=>$list];
    }

    /**
     * 根据条件建立sql语句
     * @param array $map
     * @return $this
     */
    public function getIndexDataSql($map = [])
    {
        $map['li.user_id'] = user_info('user_id');
        return Db::name('user_letter_list')->alias('li')
            ->join('UserLetter le', 'li.letter_id=le.id')
            ->where($map);
    }

}