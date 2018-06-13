<?php
// +----------------------------------------------------------------------
// | Copyright (c) 2018-2018 http://www.donglixia.net All rights reserved.
// +----------------------------------------------------------------------
// | Author: 十万马 <962863675@qq.com>
// +----------------------------------------------------------------------
// | DateTime: 2018-02-09 16:17
// +----------------------------------------------------------------------

namespace app\admin_fussen\controller;

use app\admin_fussen\model\SystemConfig;
use app\admin_fussen\parent\Controller;
use think\Cookie;
use think\Session;

class System extends Controller
{
    public function index()
    {
        //获取网站信息
        $SystemConfig = new SystemConfig();
        $wen_info_list = $SystemConfig->getWebInfoList();
        $wen_info_html = $this->contentHtml($wen_info_list);
        $this->assign('wen_info_html', $wen_info_html);

        //获取系统设置信息
        $system_config_list = $SystemConfig->getSystemConfigList();
        $system_config_html = $this->contentHtml($system_config_list);
        $this->assign('system_config_html', $system_config_html);

        return $this->fetch();
    }

    /**
     * 渲染设置内容html
     * @param $data
     * @return mixed
     */
    public function contentHtml($data)
    {
        $this->assign('data', $data);
        return $this->fetch('content_list');
    }

    /**
     * 保存
     */
    public function save()
    {
        $param = $this->request->param();
        $data = !empty($param['sys']) ? $param['sys'] : [];
        if (empty($data)) {
            $this->error('没有需要保存的数据！');
        }

        foreach ($data as $k=>$v) {
            if ($v['code'] == 'logo' && !empty($v['value'])) {
                $data[$k]['value'] = current(imgTempFileMove([$v['value']], 'admin_fussen/images/web/'));
            }
        }
        $SystemConfig = new SystemConfig();
        $SystemConfig->saveAll($data);

        setSessionConfig();//缓存系统设置
        $this->success('保存成功！');
    }

}

