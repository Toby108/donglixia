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
use think\Db;
use think\Request;

class System extends Base
{
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->currentModel = new SystemConfig();//实例化当前模型
    }

    public function index()
    {
        //获取数据列表，以child分组
        $list = $this->currentModel->getDataList();
        foreach ($list as $k=>$v) {
            $list[$k]['child_html'] = $this->contentHtml($v['child']);
        }
        $this->assign('list', $list);
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
            $this->currentModel->saveAll($data);
            setSessionConfig();//缓存系统设置
        } catch (\Exception $e) {
            $msg = !empty($this->currentModel->getError()) ? $this->currentModel->getError() : $e->getMessage();
            $this->error($msg);
        }

        $this->success('保存成功！');
    }

}

