<?php
// +----------------------------------------------------------------------
// | Copyright (c) 2018-2018 http://www.donglixia.net All rights reserved.
// +----------------------------------------------------------------------
// | Author: 十万马 <962863675@qq.com>
// +----------------------------------------------------------------------
// | DateTime: 2018-02-09 16:17
// +----------------------------------------------------------------------

namespace app\attend\controller;

use think\Controller as CoreController;
use think\Db;

class Index extends CoreController
{
    public function index()
    {
        $param = $this->request->param();
        header("Content-Type: text/html; charset=UTF-8");

        if (!empty($param['admin_id'])) {
            $admin_id = $param['admin_id'];
        } else {
            return ['status'=> false, 'msg'=>'用户id不能为空！'];
        }

        $wifi = !empty($param['wifi']) ? $param['wifi'] : '50:da:00:f5:4f:50';
        $mobile_mac = !empty($param['mobile_mac']) ? $param['mobile_mac'] : '679855037247974,679855038027378';
        $location = !empty($param['location']) ? $param['location'] : '广东省深圳市福田区振中路236';
        /*如果当前时间小于12点，则为上班卡*/
        if (date('Hi') < '1200') {
            $data = ['admin_id' => $admin_id, 'sign_in_wifi' => $wifi, 'sign_in_mac' => $mobile_mac, 'sign_in_location' => $location];
        }
        /*如果当前时间大于12点，则为下班卡*/
        else {
            $data = ['admin_id' => $admin_id, 'sign_out_wifi' => $wifi, 'sign_out_mac' => $mobile_mac, 'sign_out_location' => $location];
        }

        $config = [
            'user_agent' => 'Mozilla/5.0 (Linux; Android 8.0.0; MHA-AL00 Build/HUAWEIMHA-AL00; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/63.0.3239.111 Mobile Safari/537.36 Html5Plus/1.0'
        ];

        $url = 'http://cloud.ehuimeng.com/Home/HrAttendDetails/add';
        $row = http_curl($url, $data, $config);
        $row = json_decode($row, true);

        $real_name = !empty($row['data']['real_name']) ? '<br> 姓名：'.$row['data']['real_name'] : '';
        $res['status'] = $row['status'] == false ? false : true;
        $res['msg'] = $row['msg'].$real_name;
        dump($res);die;
    }

    /**
     * 根据真实姓名获取用户信息
     * @return array|false|\PDOStatement|string|\think\Collection
     */
    public function getUser()
    {
        header("Content-Type: text/html; charset=UTF-8");
        $param = $this->request->param();
        if (!empty($param['real_name'])) {
            $map['real_name'] = ['like', '%'.$param['real_name'].'%'];//真实姓名
        }
        if (!empty($param['user_name'])) {
            $map['user_name'] = ['like', '%'.$param['user_name'].'%'];//用户名
        }
        if (!empty($param['mobile'])) {
            $map['mobile'] = ['like', '%'.$param['mobile'].'%'];//手机号
        }
        if (!empty($param['document_number'])) {
            $map['document_number'] = ['like', '%'.$param['document_number'].'%'];//身份证号
        }
        if (empty($map)) {
            $map[] = ['exp', '1=1'];
        }
        $res = Db::table('hm_admin_users')->where($map)->field('admin_id,user_name,real_name,document_number,mobile,address')->limit(0,4)->select();
        dump($res);die;
    }
}
