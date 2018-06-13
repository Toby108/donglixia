<?php
// +----------------------------------------------------------------------
// | Copyright (c) 2018-2018 http://www.donglixia.net All rights reserved.
// +----------------------------------------------------------------------
// | Author: 十万马 <962863675@qq.com>
// +----------------------------------------------------------------------
// | DateTime: 2018-02-09 16:17
// +----------------------------------------------------------------------

namespace app\common\model;

use app\common\parent\Model;

class BasicInfo extends Model
{
    /**
     * 递归获取下级资料id集合
     * @param $id
     * @param bool $merge
     * @return array
     */
    public function getChildId($id, $merge = true)
    {
        $id = explode(',', $id);
        $ids = $this->whereIn('pid', $id)->column('basic_id');
        foreach ($ids as $k=>$v) {
            $ids = array_merge($ids, $this->getChildId($v, false));
        }
        if ($merge) $ids = array_merge($id, $ids);
        return $ids;
    }

    /**
     * 递归获取上级资料id集合
     * @param $id
     * @param bool $merge
     * @return array
     */
    public function getParentId($id, $merge = true)
    {
        static $res = [];
        $pid = $this->where('basic_id', $id)->value('pid');
        if (!empty($pid)){
            $res[] = $pid;
            $this->getParentId($pid, false);
        }
        if ($merge) array_push($res, $id);
        asort($res);
        return $res;
    }

}