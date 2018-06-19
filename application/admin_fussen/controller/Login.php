<?php
// +----------------------------------------------------------------------
// | Copyright (c) 2018-2018 http://www.donglixia.net All rights reserved.
// +----------------------------------------------------------------------
// | Author: 十万马 <962863675@qq.com>
// +----------------------------------------------------------------------
// | DateTime: 2018-02-09 16:17
// +----------------------------------------------------------------------

namespace app\admin_fussen\controller;

use think\Controller;
use app\admin_fussen\model\User;
use think\Cache;
use think\Cookie;
use think\Db;
use think\Session;

class Login extends Controller
{
    protected function _initialize()
    {
        /*一级域名，重定向到二级域名*/
        if (substr_count($_SERVER['HTTP_HOST'], '.') === 1) {
            header('Location: http://www.'.$_SERVER['HTTP_HOST']);
        }
        $web_name = Db::name('system_config')->where('sys_code', 'web_name')->value('sys_value');
        $this->assign('web_name', !empty($web_name) ? $web_name : 'CMS');
    }

    /**
     * 登录页面
     * @return mixed
     */
    public function index()
    {
        //若session已存在，则直接跳往后台主页
        if (!empty(user_info('user_id'))) {
            $this->redirect('Index/index');
        }

        return $this->fetch();
    }

    /**
     * 登录处理
     */
    public function login()
    {
        $param = $this->request->param();//获取参数
        if (empty($param['keyword']) || empty($param['user_pwd'])) {
            $this->error('用户名 或 密码不能为空！');
        }

        //验证码校验
        if( cookie('error_num') > 2 && (empty($param['vercode']) || !captcha_check($param['vercode']))){
            $this->error('验证码错误');
        };

        $param['language'] = !empty($param['language']) ? $param['language'] : 1;
        //记住密码，有效期一个月
        if(isset($param['remember']) && $param['remember'] == 1){
            Cookie::set('remember_name', $param['keyword']);
            Cookie::set('remember_pwd', $param['user_pwd']);
            Cookie::set('remember_language', $param['language']);
        };

        //记住登陆前的页面，登录成功后跳转
        Cookie::set('back_url', redirect()->restore()->getData());

        $data['nick_name|tel'] = $param['keyword'];
        $data['user_pwd'] = $param['user_pwd'];
        $this->loginGo($data);//设置session，实现登录并跳转
    }

    /**
     * 设置session，实现登录并跳转
     * @param $data
     */
    public function loginGo($data)
    {
        $password = '';
        if (!empty($data['user_pwd'])) {
            $password = $data['user_pwd'];
            unset($data['user_pwd']);
        }

        $url = !empty(Cookie::get('back_url')) ? Cookie::get('back_url') : 'Index/index';
        try{
            /*获取用户数据*/
            $User = new User();
            $userInfo = $User->getUserInfo($data, $password);

            //返回错误信息
            if(isset($userInfo['status']) && $userInfo['status'] == false){
                throw new \Exception($userInfo['msg']);
            }
            $this->setSession($userInfo);//设置session

        } catch (\Exception $e) {
            cookie('error_num', cookie('error_num') + 1);
            $this->error($e->getMessage());
        }

        Cookie::set('back_url', null);
        if ($this->request->isAjax()) {
            $this->success('登录成功！', $url);
        } else {
            $this->redirect($url);
        }
    }

    /**
     *微信登录
     */
    public function weChatLogin()
    {
        //重定向获取微信code
        $code = !empty($this->request->param('code')) ? trim($this->request->param('code')) : '';
        if (empty($code)) {
            $redirect_uri = 'http://'.$_SERVER['HTTP_HOST'].'/admin_fussen/login/wechatlogin.html';

            $url = 'https://mshop.ehuimeng.com/shop/wechat/getWechatCode.html?url='.$redirect_uri;
            header ( "Location: " . $url );
            die;
        }

        //获取当前微信账号的 wechat_unionid
        $weixin = !empty($this->request->param('weixin')) ? trim($this->request->param('weixin')) : 0;
        $url = 'https://mshop.ehuimeng.com/shop/wechat/getWechatUserInfo.html?code='.$code.'&weixin='.$weixin;
        $user_wei = http_curl($url);
        $user_wei = json_decode($user_wei, true);

        if (empty($user_wei) || $user_wei['status'] == false) {
            $msg = !empty($user_wei['msg']) ? $user_wei['msg'] : '微信账号信息，获取失败';
            $this->error($msg, 'index');
        }

        $data['nickname'] = $user_wei['data']['nickname'];
        $data['wechat_openid'] = $user_wei['data']['openid'];
        $data['wechat_unionid'] = $user_wei['data']['unionid'];
        Cookie::set('tencent',$data);
        $this->loginOrBind();//根据微信用户信息，判断直接登录或绑定用户
    }

    /**
     * QQ登录
     */
    public function QQLogin()
    {
        $code = !empty($this->request->param('code')) ? trim($this->request->param('code')) : '';
        $msg = !empty($this->request->param('msg')) ? trim($this->request->param('msg')) : '';
        $data = !empty($this->request->param('data')) ? json_decode($this->request->param('data'), true) : '';
        if (empty($code)) {
            $back_url = 'http://'.$_SERVER['HTTP_HOST'].'/admin_fussen/login/QQLogin.html';

            $url = 'http://www.donglixia.net/admin_fussen/QQ/getQQCode.html?back_url='.$back_url;
            header ( "Location: " . $url );
            die;
        } elseif ($code == '-1') {
            $this->error($msg, 'index');
        }

        Cookie::set('tencent',$data);
        $this->loginOrBind();//根据qq用户信息，判断直接登录或绑定用户
    }

    /**
     * 第三方登录，判断直接登录或绑定用户
     * @return bool
     */
    public function loginOrBind()
    {
        $tencent = Cookie::get('tencent');
        $where = [];
        if (!empty($tencent['wechat_unionid'])) {
            //微信
            $where['wechat_unionid'] = $tencent['wechat_unionid'];
        } elseif (!empty($tencent['qq_openid'])) {
            //QQ
            $where['qq_openid'] = $tencent['qq_openid'];
        } else {
            $this->error('参数错误');
        }

        $count = Db::name('user')->where($where)->count();
        if ($count == 1) {
            //已绑定用户，直接登录，跳转主页
            $this->loginGo($where);
        } elseif ($count > 1) {
            $this->error('该信息绑定多个帐号');
        } elseif ($count == 0) {
            //渲染页面，提示是否已存在帐号，进行绑定
            $this->redirect('tempHtml');
        }
    }

    /**
     * 渲染临时页面，用于弹窗提示绑定用户
     * @return mixed
     */
    public function tempHtml()
    {
        return $this->fetch('empty');
    }

    /**
     * 用户操作，点击“绑定” 或 “跳过，添加新用户”
     * @ $param['type']  1绑定，0不绑定
     * @return bool
     */
    public function bindUser()
    {
        //接收参数
        $param = $this->request->param();
        $type = !empty($param['type']) ? $param['type'] : 0;
        $tencent = Cookie::get('tencent');

        //验证参数
        if (!isset($tencent['nickname'])) {
            $this->error('授权数据获取失败');
        }
        $data = [];
        if (!empty($tencent['qq_openid'])) {
            $data['qq_openid'] = $tencent['qq_openid'];
        }
        if (!empty($tencent['wechat_openid'])) {
            $data['wechat_openid'] = $tencent['wechat_openid'];
        }
        if (!empty($tencent['wechat_unionid'])) {
            $data['wechat_unionid'] = $tencent['wechat_unionid'];
        }

        $count = Db::name('user')->where($data)->count();
        if ($count == 1) {
            //已绑定用户，直接登录，跳转主页
            $this->loginGo($data);
        }

        if ($type == 1) {
            //若现在绑定，则根据输入的帐号密码，绑定信息
            if (empty($param['tel'])) {
                $this->error('手机号不能为空');
            }

            $User = new User();
            $userInfo = $User->getUserInfo(['tel' => $param['keyword']]);
            if (isset($userInfo['status']) && $userInfo['status'] == false) {
                $this->error($userInfo['msg']);
            }
            Db::name('user')->where('user_id', $userInfo['user_id'])->update($data);
            $user_id = $userInfo['user_id'];
        } else {
            //若不绑定，则直接生成新用户
            $data['role_id'] = 2;//角色：游客
            $data['avatar'] = VIEW_IMAGE_PATH . '/avatar/user' . rand(10, 50) . '.png';//头像
            $data['nick_name'] = $tencent['nickname'];
            $user_id = Db::name('user')->insertGetId($data);
        }
        $this->loginGo(['user_id' => $user_id]);//设置session，实现登录并跳转
    }

    /**
     * 注册页面
     * @return mixed
     */
    public function register()
    {
        return $this->fetch();
    }

    /**
     * 注册处理
     */
    public function registerSave()
    {
        $param = $this->request->param();

        //数据验证
        $result = $this->validate($param, 'User');
        if ($result !== true) {
            $this->error($result, null, ['token'=>$this->request->token()]);
        }

        //保存数据
        $User = model('User');
        $row = $User->save($param);

        //返回错误
        if ($row === false) {
            $this->error($User->getError(), null, ['token'=>$this->request->token()]);
        }

        $this->success('注册成功！');
    }

    /**
     * 忘记密码，找回密码页面
     * @return mixed
     */
    public function forget()
    {
        return $this->fetch();
    }

    /**
     * 重置密码页面
     * @return mixed
     */
    public function passwordReset()
    {
        return $this->fetch();
    }

    /**
     * 保存密码
     */
    public function passwordSave()
    {
        $this->success('操作成功！');
    }

    /**
     * 退出登录
     */
    public function loginOut()
    {
        Session::clear();
        $this->redirect('index');
    }

    /**
     * 保存用户信息到session
     * @param $data
     */
    public function setSession($data)
    {
        //用户信息
        if (is_object($data)) {
            $data = collection($data)->toArray();
        }
        Session::set('userInfo', $data);

        //语言：1中文，2English
        $lang = Cookie::get('remember_language') ? Cookie::get('remember_language') : 1;
        Db::name('system_config')->where('sys_code', 'language')->update(['sys_value'=>$lang]);
        setSessionConfig();//缓存系统设置

        cookie('error_num', 0);//错误次数归0
    }

    /**
     * 清除缓存
     */
    public function clearCache()
    {
        if(function_exists('opcache_reset'))
        {
            opcache_reset();
        }
        Cache::clear();
        array_map('unlink',glob(TEMP_PATH.DS.'*.php'));
        $this->success('清除缓存成功');
    }
}