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

class UserRole extends Base
{
    /**
     * 保存字段“权限明细”
     * @param $value
     * @return mixed
     */
    protected function setAuthAttr($value){
        if(is_array($value)){
            $value = implode(',',$value);
        }
        return $value;
    }
}