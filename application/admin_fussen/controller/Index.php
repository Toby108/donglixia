<?php
// +----------------------------------------------------------------------
// | Copyright (c) 2018-2018 http://www.donglixia.net All rights reserved.
// +----------------------------------------------------------------------
// | Author: 十万马 <962863675@qq.com>
// +----------------------------------------------------------------------
// | DateTime: 2018-02-09 16:17
// +----------------------------------------------------------------------

namespace app\admin_fussen\controller;

use app\admin_fussen\parent\Controller;
use think\Session;
use think\Db;

class Index extends Controller
{
    public function index()
    {
        //文章数
        $article_count = Db::name('article')->count();
        $this->assign('article_count', $article_count);

        //产品数
        $goods_count = Db::name('goods')->count();
        $this->assign('goods_count', $goods_count);

        //用户数
        $user_count = Db::name('user')->count();
        $this->assign('user_count', $user_count);

        //阅读数
        $article_read_num = Db::name('article')->sum('read_num');
        $goods_read_num = Db::name('goods')->sum('read_num');
        $this->assign('read_num', $article_read_num + $goods_read_num);
        return $this->fetch();
    }

    public function edit()
    {
        return $this->fetch();
    }

    /**
     * 切换语言
     * @param int $type
     */
    public function changeLanguage($type = 1)
    {
        Session::set('config.language', $type);
        Db::name('system_config')->where('area_code', 'language')->update(['area_value'=>$type]);
        $this->success('切换成功');
    }
}

