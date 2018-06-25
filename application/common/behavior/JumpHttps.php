<?php
namespace app\common\behavior;
use think\Request;
class JumpHttps
{
    public function run()
    {
        $request = Request::instance();
        if(!($request->isSsl()))
        {
//            header("Location:"."https://".$request->server('SERVER_NAME').$request->url());
        }
    }
}