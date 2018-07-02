<?php
// +----------------------------------------------------------------------
// | Copyright (c) 2018-2018 http://www.donglixia.net All rights reserved.
// +----------------------------------------------------------------------
// | Author: 十万马 <962863675@qq.com>
// +----------------------------------------------------------------------
// | DateTime: 2018-02-09 16:17
// +----------------------------------------------------------------------

namespace app\admin_fussen\model;

use app\common\model\Base;
use think\Db;

class UserDept extends Base
{
    /**
     * 获取部门下拉列表，子部门以[children]分组
     * @return array
     */
    public function getDeptTree()
    {
        /*获取下拉列表：部门*/
        $deptList = Db::name('user_dept')->where('state', 1)->field('dept_id as id,pid,dept_name as name')->order('sort_num')->select();
        $deptList = \Tree::getTree($deptList, 'id', 'pid', 'children');
        return $deptList;
    }

    /**
     * 获取部门列表，树状Table_tree
     */
    public function getDeptTableTree()
    {
        $deptList = $this->field('dept_id,pid,dept_name')->select();
        return \Tree::get_Table_tree($deptList, 'dept_name', 'dept_id');
    }
}