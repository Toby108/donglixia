<?php
// +----------------------------------------------------------------------
// | Copyright (c) 2018-2018 http://www.donglixia.net All rights reserved.
// +----------------------------------------------------------------------
// | Author: 十万马 <962863675@qq.com>
// +----------------------------------------------------------------------
// | DateTime: 2018-02-09 16:17
// +----------------------------------------------------------------------

namespace app\admin_fussen\controller;

use app\admin_fussen\model\UserDept;
use app\admin_fussen\model\UserRole;
use app\admin_fussen\model\User as UserModel;
use app\admin_fussen\model\BasicInfo as BasicInfoModel;
use app\admin_fussen\parent\Controller;
use think\Db;
use think\Session;
use think\Request;

class User extends Controller
{
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->currentModel = new UserModel();//实例化当前模型
    }

    /**
     * 列表页
     * @return mixed
     */
    public function index()
    {
        return $this->fetch();
    }

    /**
     * 列表页，获取数据
     * @return mixed
     */
    public function getDataList()
    {
        $map = $this->getDataListMap();
        return $this->currentModel->where($map)->layTable();
    }

    private function getDataListMap()
    {
        $param = $this->request->param();
        if (!empty($param['nick_name'])) {
            $map['nick_name'] = ['like', '%' . $param['nick_name'] . '%'];//帐号
        }
        if (!empty($param['real_name'])) {
            $map['real_name'] = ['like', '%' . $param['real_name'] . '%'];//真实姓名
        }
        if (!empty($param['tel'])) {
            $map['tel'] = ['like', '%' . $param['tel'] . '%'];//手机号
        }
        if (!empty($param['card_no'])) {
            $map['card_no'] = ['like', '%' . $param['card_no'] . '%'];//证件号
        }
        if (empty($map)) {
            $map[] = ['exp', '1=1'];
        }
        return $map;
    }

    /**
     * 编辑
     * @return mixed
     */
    public function edit()
    {
        $param = $this->request->param();
        $uid = !empty($param['uid']) ? $param['uid'] : '';
        if (!empty($uid)) {
            /*获取当前人员信息*/
            $data = $this->currentModel->where('uid', $uid)->find();
            $this->assign('data', $data);

            /*获取下拉列表：市*/
            $city = $this->getRegion($data['province']);
            $this->assign('cityList', $city);

            /*获取下拉列表：区/镇*/
            $district = $this->getRegion($data['city']);
            $this->assign('districtList', $district);
        }

        /*随机头像*/
        $avatar = !empty($data['avatar']) ? $data['avatar'] : VIEW_IMAGE_PATH . '/avatar/user' . rand(10, 50) . '.png';
        $this->assign('avatar', $avatar);

        /*获取下拉列表：角色权限*/
        $roleList = (new UserRole())->field('role_id,role_name,describe')->select();
        $this->assign('roleList', $roleList);

        /*获取下拉列表：部门*/
        $deptList = (new UserDept())->field('dept_id,pid,dept_name')->select();
        $deptList = \Tree::get_Table_tree($deptList, 'dept_name', 'dept_id');
        $this->assign('deptList', $deptList);

        /*获取下拉列表：推荐人*/
        $parentList = $this->currentModel->getParentList($uid);
        $this->assign('parentList', $parentList);

        /*获取下拉列表：省份*/
        $provinceList = $this->getRegion(0);
        $this->assign('provinceList', $provinceList);

        /*获取下拉列表：证件类型，银行，民族，学历，政治面貌等*/
        $BasicInfo = new BasicInfoModel();
        $basicList = $BasicInfo->getPersonList();
        $this->assign('basicList', $basicList);

        /*获取相关图片列表*/
        $imgList = $BasicInfo->getBasicList('person', 'AJ');
        foreach ($imgList as $k => $v) {
            $img = Db::name('user_image')->where('uid', $uid)->where('type', $v['basic_id'])->field('img_id,uid,type,img_url')->find();
            $imgList[$k]['uid'] = $uid;
            $imgList[$k]['type'] = $v['basic_id'];
            $imgList[$k]['img_id'] = !empty($img['img_id']) ? $img['img_id'] : '';
            $imgList[$k]['img_url'] = !empty($img['img_url']) ? $img['img_url'] : '';
        }
        $this->assign('imgList', $imgList);

        return $this->fetch();
    }

    /**
     * 保存
     */
    public function save()
    {
        $param = $this->request->param();
        if (empty($param)) {
            $this->error('没有需要保存的数据！');
        }

        //验证数据
        $result = $this->validate($param, 'User');
        if ($result !== true) {
            $this->error($result, null, ['token' => $this->request->token()]);
        }

        try {
            //压缩头像100*100
            $param['avatar'] = current(imgTempFileMove([$param['avatar']], 'admin_fussen/images/user/'));
            if (strpos($param['avatar'], 'user/') && file_exists(PUBLIC_PATH . $param['avatar'])) {
                \think\Image::open(PUBLIC_PATH . $param['avatar'])->thumb(100, 100)->save(PUBLIC_PATH . $param['avatar'], null, 100);
            }
            //保存主表数据
            $this->currentModel->save($param);

            //保存子表：图片
            if (!empty($param['img'])) {
                foreach ($param['img'] as $k => $v) {
                    //将图片文件从 img/temp 文件夹，移到 img/user 文件夹中
                    $res = current(imgTempFileMove([$v['img_url']], 'admin_fussen/user'));
                    $param['img'][$k]['img_url'] = $res;
                }
                $this->currentModel->userImage()->saveAll($param['img']);
            }
        } catch (\Exception $e) {
            $msg = !empty($this->currentModel->getError()) ? $this->currentModel->getError() : $e->getMessage();
            $this->error($msg, null, ['token' => $this->request->token()]);
        }

        //如果是本人修改自己的资料，则刷新session
        if ($this->currentModel->uid == user_info('uid')) {
            $userInfo = $this->currentModel->getUserInfo(['uid'=>user_info('uid')]);
            session('userInfo', $userInfo);//刷新session
        }

        $this->success('保存成功！', 'edit?uid=' . $this->currentModel->uid);
    }

    /**
     * 更新某个字段
     */
    public function updateField()
    {
        $param = $this->request->param();
        if (empty($param['uid'])) {
            $this->error('用户id不能为空');
        }

        //验证数据
        $result = $this->validate($param, 'User.updateField');
        if ($result !== true) {
            $this->error($result);
        }

        $res = $this->currentModel->isUpdate(true)->save($param);
        if ($res === false) {
            $this->error($this->currentModel->getError());
        }
        $this->success('更新成功!');
    }

    /**
     * 修改密码
     */
    public function changePassword()
    {
        $param = $this->request->param();

        if (empty($param['user_pwd'])) {
            $this->error('新密码不能为空');
        }

        if ($param['user_pwd'] != $param['confirm']) {
            $this->error('两次输入的密码不一致');
        }

        if (!password_strength($param['user_pwd'])) {
            $this->error('密码太简单，请重新修改');
        }

        //保存数据
        $res = $this->currentModel->save($param);
        if ($res === false) {
            $this->error($this->currentModel->getError());
        }

        //登录者密码修改成功，刷新缓存Session
        if ($param['uid'] == user_info('uid')) {
            Session::set('userInfo.password_strong', 1);
        }

        $this->success('保存成功!');
    }

    /**
     * 根据pid 获取下拉列表，省市区三级联动
     * @param $pid
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\exception\DbException
     */
    public function getRegion($pid)
    {
        $pid = !empty($pid) ? $pid : 0;
        return Db::name('basic_region')->where('parent_id', $pid)->field('id,area_name,area_code')->select();
    }


    /**
     * 导出数据
     */
    public function exportData()
    {
        $i = 0;
        $data = [];
        $map = $this->getDataListMap();
        $this->currentModel->where($map)->field('uid,nick_name,real_name,tel,card_type,card_no,sex,create_time,login_state')
            ->chunk(100, function ($res) use (&$i, &$data) {
                $res = collection($res)->append(['sex_text', 'card_type_text', 'login_state_text'])->toArray();
                foreach ($res as $v) {
                    $data[0]['content'][$i]['num'] = $i + 1;
                    $data[0]['content'][$i]['nick_name'] = $v['nick_name'];
                    $data[0]['content'][$i]['real_name'] = $v['real_name'];
                    $data[0]['content'][$i]['tel'] = $v['tel'];
                    $data[0]['content'][$i]['card_type_text'] = $v['card_type_text'];
                    $data[0]['content'][$i]['card_no'] = $v['card_no'];
                    $data[0]['content'][$i]['sex'] = $v['sex_text'];
                    $data[0]['content'][$i]['create_time'] = $v['create_time'];
                    $data[0]['content'][$i]['login_state'] = $v['login_state_text'];
                    $i++;
                }
            });
        $data[0]['title'] = ['序号', '用户账号', '姓名', '联系电话', '证件类型	', '证件号码', '性别', '注册日期', '状态'];
        export_excel($data, date('Y-m-d'));
    }

}

