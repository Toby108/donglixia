<?php
namespace app\admin_fussen\controller;

use think\Controller;
use think\Loader;

class Wechat extends Controller
{
    /**
     * 用户授权获取微信code
     * @param string $url
     */
    public function getWechatCode ($url = 'getWechatUserInfo')
    {
        $code = !empty($this->request->param('code')) ? trim($this->request->param('code')) : '';
        //重定向获取微信code
        if (empty($code)) {
            $url = 'https://'.$_SERVER['HTTP_HOST'].'/shop/wechat/getWechatCode.html?url='.$url;
            if (is_weixin()) {
                //微信环境
                Loader::import('wechat.Wechat');
                \Wechat::validate($url, 'snsapi_userinfo');
            } else {
                //浏览器网页环境
                Loader::import('wechat.WechatWeb');
                \WechatWeb::validate($url);//重定向获取微信code
            }
            die;
        }

        //已获取到code，重定向到来源url
        $weixin = is_weixin() == true ? 1 : 0;
        $url = $url.'?code='.$code.'&weixin='.$weixin;
        $this->redirect($url);
    }

    /**
     * 获取微信用户信息
     * @return array
     */
    public function getWechatUserInfo ()
    {
        //获取当前微信账号的 wechat_unionid
        $code = !empty($this->request->param('code')) ? trim($this->request->param('code')) : '';
        $weixin = !empty($this->request->param('weixin')) ? trim($this->request->param('weixin')) : 0;
        if (empty($code)) {
            return ['code' => false, 'msg' => 'code不能为空'];
        }

        if ($weixin == 1) {
            //微信环境
            Loader::import('wechat.Wechat');
            $user_wei = \Wechat::getUserInfo($code);
        } else {
            //浏览器网页环境
            Loader::import('wechat.WechatWeb');
            $user_wei = \WechatWeb::getUserInfo($code);
        }

        if (empty($user_wei['unionid'])) {
            return json(['status' => false, 'msg' => '微信账号信息，获取失败']);
        }
        return json(['status' => true, 'msg' => '获取成功', 'data' => $user_wei]);
    }

}








