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
use think\Db;

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

        $SystemConfig = new SystemConfig();
        try {
            foreach ($data as $k=>$v) {
                if ($v['sys_code'] == 'web_logo' && !empty($v['sys_value'])) {
                    $data[$k]['sys_value'] = current(imgTempFileMove([$v['sys_value']], 'admin_fussen/images/web/'));
                    $sys_value = Db::name('system_config')->where('sys_code', 'web_logo')->value('sys_value');
                    if (!empty($sys_value) && $sys_value != $v['sys_value']) {
                        delete_file($sys_value);
                    }
                }
            }
            $SystemConfig->saveAll($data);
            setSessionConfig();//缓存系统设置
        } catch (\Exception $e) {
            $msg = !empty($SystemConfig->getError()) ? $SystemConfig->getError() : $e->getMessage();
            $this->error($msg);
        }

        $this->success('保存成功！');
    }

}

