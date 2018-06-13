<?php
// +----------------------------------------------------------------------
// | Copyright (c) 2018-{2018} http://www.donglixia.net All rights reserved.
// +----------------------------------------------------------------------
// | Author: 十万马 <962863675@qq.com>
// +----------------------------------------------------------------------
// | DateTime: 2018/6/3 12:26
// +----------------------------------------------------------------------

namespace app\admin_fussen\controller;

use think\Controller;

class Qq extends Controller
{
    /**
     * 输入qq帐号+密码，确认授权
     * @param string $back_url
     */
    public function  getQQCode($back_url = '')
    {
        cookie('QQ_back_url', $back_url);
        require_once (EXTEND_PATH."/qqConnect2.1/API/qqConnectAPI.php");
        $qc = new \QC();
        $qc->qq_login();
    }

    /**
     * 授权后回调，执行登录操作
     * @return \think\response\Json
     */
    public function  connectCallBack()
    {
        require_once (EXTEND_PATH."/qqConnect2.1/API/qqConnectAPI.php");
        //获取qq_openid 和 qq用户信息
        $qc = new \QC();
        $acs = $qc->qq_callback();
        $openid =  $qc->get_openid();

        $qc = new \QC($acs,$openid);
        $qq_user = $qc->get_user_info();
        if (empty($qq_user)) {
            $url = cookie('QQ_back_url').'?code=-1&msg=qq账号信息，获取失败';
        } else {
            $qq_user['qq_openid'] = $openid;
            $url = cookie('QQ_back_url'). "?code=1&msg=获取成功&data=".json_encode($qq_user);
        }
        header ( "Location: " . $url );
        die;
    }
}