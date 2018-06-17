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
use think\Db;

class User extends Model
{

    /**
     * 获取出生日期
     * @param $value
     * @return false|string
     */
    public function getBirthdayAttr($value)
    {
        return $value == '0000-00-00' ? '' : $value;
    }

    /**
     * 保存出生日期
     * @param $value
     * @return false|string
     */
    public function setBirthdayAttr($value)
    {
        return $this->saveToYyyyMmDd($value);
    }

    /**
     * 保存为 年-月-日
     * @param $data
     * @return false|string
     */
    public function saveToYyyyMmDd($data)
    {
        if (empty($data)) {
            $res = '0000-00-00';
        } elseif (is_int($data)) {
            $res = date('Y-m-d', $data);
        } else {
            $res = $data;
        }
        return $res;
    }

    /**
     * 获取性别中文称呼
     * @param $value
     * @return string
     */
    public function getSexTextAttr($value, $data)
    {
        return $data['sex'] ? '男' : '女';
    }

    /**
     * 获取字段“证件类型”中文名称
     * @param $value
     * @param $data
     * @return false|string
     */
    public function getCardTypeTextAttr($value, $data)
    {
        return Db::name('basic_info')->where('basic_id', $data['card_type'])->value('basic_name');
    }

    /**
     * 获取登录状态“login_mk” 中文名称
     * @param $value
     * @param $data
     * @return mixed
     */
    public function getLoginStateTextAttr($value, $data)
    {
        $item = ['-1'=>'待入职', '0'=>'冻结', '1'=>'正常', '2'=>'调试'];
        return $item[$data['login_state']];
    }

}