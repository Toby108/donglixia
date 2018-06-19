<?php
// +----------------------------------------------------------------------
// | Copyright (c) 2018-2018 http://www.donglixia.net All rights reserved.
// +----------------------------------------------------------------------
// | Author: 十万马 <962863675@qq.com>
// +----------------------------------------------------------------------
// | DateTime: 2018-02-09 16:17
// +----------------------------------------------------------------------

namespace app\admin_fussen\parent;

use app\admin_fussen\model\BasicMenu;
use app\admin_fussen\model\User;
use app\common\parent\Controller as CoreController;
use think\Cookie;
use think\Db;
use think\Session;
use think\Request;

class Controller extends CoreController
{
    public $currentModel;

    public function _initialize()
    {

        $param = $this->request->param();//获取参数
        if (substr_count($_SERVER['HTTP_HOST'], '.') === 1) {
            header('Location: http://www.'.$_SERVER['HTTP_HOST']);
        }

        /*游客体验，直接进入后台*/
        if (isset($param['uid']) && isset($param['visitor']) && $param['uid'] == '2' && $param['visitor'] =='1') {
            Session::set('userInfo', (new User())->getUserInfo(['uid'=>$param['uid']]));
        }

        /*判断是否已登录*/
        if (empty(user_info('uid'))) {
            $this->redirect('Login/index');
        }

        //获取当前链接菜单信息，渲染当前左侧菜单显示效果
        $BasicMenu = new BasicMenu();
        $menuCurrent = $BasicMenu->getMenuCurrent();
        $this->assign('menuCurrent', $menuCurrent);

        //面包屑
        $breadcrumb = $BasicMenu->get_breadcrumb($menuCurrent['menu_id']);
        $this->assign('breadcrumb', $breadcrumb);

        if (!$this->checkAuth($menuCurrent['menu_id']) && $menuCurrent['menu_id'] != 1) {
            $this->error('您没有权限操作', 'Index/index');
        }

        //获取当前登录用户菜单列表
        $menuList = $BasicMenu->getMenuList();
        $this->assign('menu', $menuList);

        //将数据列表页显示的行数设置为Cookie缓存
        if (!empty($param['limit']) && $param['limit']<99999 && $param['limit'] != Cookie::get('table_limit')) {
            Cookie::forever('table_limit', $param['limit']);
        }
        $this->assign('table_limit', Cookie::get('table_limit'));
    }

    /**
     * 权限验证
     * @param $menu_id string 当前菜单id
     * @return bool
     */
    private function checkAuth($menu_id)
    {
        /*admin账号 或 更改登录者自己的数据，直接通过验证*/
        $uid = $this->request->param('uid');
        $action = $this->request->action();

        if ($uid == (user_info('uid')) && ($action == 'edit' || $action == 'changepassword'))
            return true;
        if ((user_info('nick_name') == 'admin') || (user_info('uid') == 1))
            return true;

        //获取该账户对应的所有角色权限，并格式化为数组形式
        $auth = model('UserRole')->whereIn('role_id', user_info('role_id'))->column('auth');
        $authList = explode(',', implode(',', $auth));

        //获取菜单管理中的菜单id列表
        $menuAll = model('BasicMenu')->column('menu_id');

        /*验证 菜单id列表 是否包含 当前菜单id。包含则继续验证；不包含则直接通过 */
        if (in_array($menu_id, $menuAll)) {
            /*验证 权限菜单id列表 是否包含 当前菜单id。包含则通过*/
            if (in_array($menu_id, array_unique($authList))) {
                return true;
            }
            return false;
        }
        return true;
    }

    /**
     * 渲染编辑页面
     * @return mixed
     */
    public function edit()
    {
        return $this->fetch();
    }

    /**
     * 删除，并记录日志
     * @param $id
     */
    public function delete($id)
    {
        try{
            //若存在pid字段，则先删除子部门资料
            $table_name = $this->currentModel->getTableName();
            if (has_field($table_name, 'pid')) {
                $data_child = Db::name($table_name)->whereIn('pid', $id)->select();
                if (!empty($data_child)) {
                    foreach ($data_child as $k => $v) {
                        $this->currentModel->dataChangelog($v, 3);//记录删除日志
                    }
                    $this->currentModel->whereIn('pid', $id)->delete();
                }
            }

            //删除当前资料，并记录删除日志
            $pk = $this->currentModel->getPk();
            $data = Db::name($table_name)->where($pk, $id)->find();
            $this->currentModel->dataChangelog($data, 3);//记录删除日志
            $this->currentModel->whereIn($pk, $id)->delete();//删除当前资料
        } catch (\Exception $e) {
            $msg = !empty($this->currentModel->getError()) ? $this->currentModel->getError() : $e->getMessage();
            $this->error($msg);
        }
        $this->success('删除成功!');
    }

    /**
     * 更改排序
     */
    public function changeSort()
    {
        $param = $this->request->param();
        if (empty($param['id']) || empty($param['type'])) {
            $this->error('参数错误');
        }

        try {
            $table_name = $this->currentModel->getTableName();//获取表名
            $list = reset_sort($param['id'], $table_name, $param['type']);//格式化，获取重新排序的数据
            $this->currentModel->saveAll($list);//保存数据
        } catch (\Exception $e) {
            $msg = !empty($this->currentModel->getError()) ? $this->currentModel->getError() : $e->getMessage();
            $this->error($msg);
        }

        $this->success('操作成功');
    }

    /**
     * @note上传图片
     */
    public function uploadImg()
    {
        $file = Request::instance()->file('file');
        if (empty($file)) {
            $this->error('上传数据为空');
        } else {
            $info = $file->move(IMAGE_PATH . '/temp/', time() . rand(100, 999));
            if ($info == false) {
                $this->error($file->getError());
            } else {
                $image = VIEW_IMAGE_PATH . '/temp/' . $info->getSaveName();
                $this->success('上传成功', null, $image);
            }
        }
    }

}