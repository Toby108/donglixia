<?php
// +----------------------------------------------------------------------
// | Copyright (c) 2018-2018 http://www.donglixia.net All rights reserved.
// +----------------------------------------------------------------------
// | Author: 十万马 <962863675@qq.com>
// +----------------------------------------------------------------------
// | DateTime: 2018-02-09 16:17
// +----------------------------------------------------------------------

namespace app\admin_fussen\model;
use app\common\model\BasicMenu as ComBasicMenu;
use think\Db;
use think\Request;

class BasicMenu extends ComBasicMenu
{
    /**
     * 获取“显示”字段，中文名称
     * @param $value
     * @return string
     */
    protected function getDisplayTextAttr($value)
    {
        return $value == '1' ? '是' : '否';
    }

    /**
     * 获取“默认展开”字段，中文名称
     * @param $value
     * @return string
     */
    protected function getExtendTextAttr($value)
    {
        return $value == '1' ? '是' : '否';
    }

    /**
     * 获取“打开方式”字段，中文名称
     * @param $value
     * @return string
     */
    protected function getOpenTypeTextAttr($value)
    {
        return $value == '1' ? '当前窗口' : '新窗口';
    }

    /**
     * 获取完整链接地址
     * @param $value
     * @param $data
     * @return string
     */
    protected function getUrlTextAttr($value, $data)
    {
        $res = $value;
        if (empty($value)) {
            $res = '#';
        } elseif (($value != '#') && (strpos($value, 'http') === false)) {
            $res = url($value, $data['params']);
        }
        return $res;
    }


    /**
     * 获取“pid” 对应的中文名称
     * @param $value
     * @return mixed
     */
    protected function getPidTextAttr($value)
    {
        return !empty($value) ? Db::name('basic_menu')->where('menu_id', $value)->value('name') : '顶级';
    }

    protected function setSortAttr($value, $data)
    {
        $res = $value;
        if (empty($value) && !empty($data['pid'])) {
            $max = Db::name('basic_menu')->where('pid', $data['pid'])->max('sort');
            $res = $max+1;
        }
        return $res;
    }

    /**
     * 获取面包屑
     * @param $id
     * @param array $res
     * @return string
     * @throws \think\exception\DbException
     */
    public function get_breadcrumb($id, &$res = [])
    {
        $data = $this->where('menu_id', $id)->field('menu_id,pid,name,url as url_text,params')->find();
        $res[$data['menu_id']] = (!empty($data['url_text']) && ($data['url_text'] != '#')) ? "<a href='{$data['url_text']}'>{$data['name']}</a>" : "<a>{$data['name']}</a>";
        if ($data['pid'] != 0) {
            $res[$data['pid']] = $this->get_breadcrumb($data['pid']);
        }
        ksort($res);
        return implode('', $res);
    }

    /**
     * 获取当前登录用户的菜单列表
     * @return array|false|\PDOStatement|string|\think\Collection
     * @throws \think\exception\DbException
     */
    public function getMenuList()
    {
        $map = [];
        $map['display'] = 1;
        if (user_info('uid') != 1) {
            $auth = model('UserRole')->whereIn('role_id', user_info('role_id'))->column('auth');
            $map['menu_id'] = ['in', implode(',', $auth)];
        }
        $menuList = $this->where($map)
            ->field('menu_id,pid,name,url as url_text,params,open_type,extend')
            ->order('sort asc')
            ->select();
        $menuList = \Tree::getTree($menuList, 'menu_id');
        return $menuList;
    }

    /**
     * 获取当前链接菜单信息
     * @return array|false|\PDOStatement|string|\think\Model
     * @throws \think\exception\DbException
     */
    public function getMenuCurrent()
    {
        //获取模块名，控制器名，方法名
        $request = Request::instance();
        $controller = $request->module(). '/' .$request->controller(). '/' .$request->action();
        //获取完整链接
        $http_url = $_SERVER['HTTP_HOST'].$request->url();

        //获取当前链接的menu表主键id
        $res = Db::name('basic_menu')
            ->whereLike('url', '%'.$http_url)
            ->whereOr('', 'exp', "url='$controller' AND LOCATE(params,'".http_build_query($request->get())."') > 0")
            ->field('menu_id,menu_id as id_display,pid,pid as pid_display,display,extend')
            ->find();
        /*若当前链接菜单为隐藏，则继续查找，页面左侧菜单定位在父级菜单*/
        if ($res['display'] == 2) {
            $res_display = Db::name('basic_menu')
                ->where('menu_id', $res['pid'])
                ->field('menu_id as id_display,pid as pid_display')
                ->find();
            $res = array_merge($res, $res_display);
        }
        return $res;
    }

    /**
     * 重新排序
     * @param $id
     * @param $type
     * @return mixed
     */
    public function resetSort($id, $type)
    {
        //获取同级别的菜单数据
        $pid = Db::name('basic_menu')->where('menu_id', $id)->value('pid');
        $data = Db::name('basic_menu')->where('pid', $pid)->field('menu_id,sort')->order('sort asc')->select();

        //将序号重新按1开始排序
        foreach ($data as $key => $val) {
            $data[$key]['sort'] = $key+1;
        }
        //处理更改排序操作
        foreach ($data as $key => $val) {
            if ($type == 'asc') {
                if (($key == '0') && $val['menu_id'] == $id) {
                    break;//首位菜单 点升序，直接中断
                }
                //升序操作：当前菜单序号减一，前一位的序号加一
                if ($val['menu_id'] == $id) {
                    $data[$key-1]['sort']++;
                    $data[$key]['sort']--;
                    break;
                }
            } elseif ($type == 'desc') {
                if (($key == count($data)) && $val['menu_id'] == $id) {
                    break;//末位菜单 点降序，直接中断
                }
                //降序操作：当前菜单序号加一，后一位的序号减一
                if ($val['menu_id'] == $id && isset($data[$key+1])) {
                    $data[$key]['sort']++;
                    $data[$key+1]['sort']--;
                    break;
                }
            }
        }
        return $data;
    }


}