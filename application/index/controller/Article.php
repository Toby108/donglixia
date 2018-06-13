<?php
// +----------------------------------------------------------------------
// | Copyright (c) 2018-2018 http://www.donglixia.net All rights reserved.
// +----------------------------------------------------------------------
// | Author: 十万马 <962863675@qq.com>
// +----------------------------------------------------------------------
// | DateTime: 2018-02-09 16:17
// +----------------------------------------------------------------------

namespace app\index\controller;

use think\Controller;
use app\index\model\Article as ArticleModel;
use think\Request;

class Article extends Controller
{
    /**
     * 列表页
     * @return mixed
     */
    public function index()
    {
        return $this->fetch();
    }




}

