<?php
// +----------------------------------------------------------------------
// | Copyright (c) 2018-2018 http://www.donglixia.net All rights reserved.
// +----------------------------------------------------------------------
// | Author: 十万马 <962863675@qq.com>
// +----------------------------------------------------------------------
// | DateTime: 2018-02-09 16:17
// +----------------------------------------------------------------------

namespace app\admin_fussen\controller;

use think\Session;
use think\Db;

class Index extends Base
{
    public function index()
    {
        //文章数
        $article = [];
        $article['count'] = Db::name('article')->count();
        $article['count_month'] = Db::name('article')->where('create_time', '>=', strtotime(date('Y-m-01')))->count();
        $this->assign('article', $article);

        //产品数
        $goods = [];
        $goods['count'] = Db::name('goods')->count();
        $goods['count_month'] = Db::name('goods')->where('create_time', '>=', strtotime(date('Y-m-01')))->count();
        $this->assign('goods', $goods);

        //用户数
        $user = [];
        $user['count'] = Db::name('user')->count();
        $user['count_month'] = Db::name('user')->where('create_time', '>=', strtotime(date('Y-m-01')))->count();
        $this->assign('user', $user);

        //阅读数
        $active['read_num'] = Db::name('goods')->sum('read_num') + Db::name('article')->sum('read_num');//阅读数
        $active['praise_num'] = Db::name('goods')->sum('praise_num') + Db::name('article')->sum('praise_num');//点赞数
        $this->assign('active', $active);

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

