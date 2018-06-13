<?php
// +----------------------------------------------------------------------
// | Copyright (c) 2018-2018 http://www.donglixia.net All rights reserved.
// +----------------------------------------------------------------------
// | Author: 十万马 <962863675@qq.com>
// +----------------------------------------------------------------------
// | DateTime: 2018-02-09 16:17
// +----------------------------------------------------------------------

namespace app\admin_fussen\model;

use app\common\parent\Model;

class GoodsCat extends Model
{

    /**
     * 递归获取上级资料id集合
     * @param $id
     * @param bool $merge
     * @return array
     */
    public function getParentId($id, $merge = true)
    {
        static $res = [];
        $pid = $this->where('cat_id', $id)->value('pid');
        if (!empty($pid)){
            $res[] = $pid;
            $this->getParentId($pid, false);
        }
        if ($merge) array_push($res, $id);
        asort($res);
        return $res;
    }

}