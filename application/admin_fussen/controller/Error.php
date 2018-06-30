<?php
// +----------------------------------------------------------------------
// | Copyright (c) 2018-2018 http://www.donglixia.net All rights reserved.
// +----------------------------------------------------------------------
// | Author: 十万马 <962863675@qq.com>
// +----------------------------------------------------------------------
// | DateTime: 2018-02-09 16:17
// +----------------------------------------------------------------------

namespace app\admin_fussen\controller;

use app\admin_fussen\parent\Base;

class Error extends Base
{
    public function index()
    {
        $url = $this->request->domain() . '/' . $this->request->module() . '/Login/clearcache?jump_wait=0';
        $this->assign('url', $url);

        $path = $this->request->module() . '/' . $this->request->controller() . '/' . $this->request->action();
        $this->assign('message', '控制器不存在：' . $path);
        return $this->fetch('../../../public/static/404');
    }


}

