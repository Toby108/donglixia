<?php
// +----------------------------------------------------------------------
// | Copyright (c) 2018-2018 http://www.donglixia.net All rights reserved.
// +----------------------------------------------------------------------
// | Author: 十万马 <962863675@qq.com>
// +----------------------------------------------------------------------
// | DateTime: 2018-02-09 16:17
// +----------------------------------------------------------------------

namespace app\admin_fussen\controller;

use app\admin_fussen\parent\Controller;
use app\admin_fussen\model\Article as ArticleModel;
use app\admin_fussen\model\ArticleCat as ArticleCatModel;
use app\admin_fussen\model\BasicInfo as BasicInfoModel;
use think\Db;
use think\Request;
use think\Cookie;
use think\Session;

class Article extends Controller
{
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->currentModel = new ArticleModel();//实例化当前模型
    }

    /**
     * 列表页
     * @return mixed
     */
    public function index()
    {
        /*获取文章状态*/
        $BasicInfo = new BasicInfoModel();
        $stateList = $BasicInfo->getBasicList('article', 'AA');
        $this->assign('stateList' ,$stateList);
        return $this->fetch();
    }

    /**
     * 列表页，获取数据
     * @return mixed
     */
    public function getDataList()
    {
        $map = $this->getDataListMap();
        return $this->currentModel->where($map)->order('sort_num asc, art_id desc')->layTable(['cat_name','state_text','public_date']);
    }

    private function getDataListMap()
    {
        $param = $this->request->param();
        if (!empty($param['cat_id'])) {
            $map['cat_id'] = $param['cat_id'];//栏目
        }
        if (!empty($param['title'])) {
            $map['title'] = ['like', '%' . $param['title'] . '%'];//标题
        }
        if (!empty($param['content'])) {
            $map['content'] = ['like', '%' . $param['content'] . '%'];//内容
        }
        if (!empty($param['state'])) {
            $map['state'] = $param['state'];//状态
        }
        if (!empty($param['public_begin']) && !empty($param['public_end'])) {
            $map['public_time'] = ['between', [strtotime($param['public_begin']),strtotime($param['public_end'])]];//公布时间
        }

        $map['language'] = !empty(Session::get('config.language')) ? Session::get('config.language') : 1;
        return $map;
    }
    
    public function edit()
    {
        $param = $this->request->param();
        $art_id = !empty($param['art_id']) ? $param['art_id'] : '';
        if (!empty($art_id)) {
            //获取当前文章信息
            $data = $this->currentModel->where('art_id', $art_id)->field(true)->field('public_time as public_date_hh_ii_ss')->find()->toArray();
            //文章栏目，级联选择格式化：“*/*”
            $data['cat_id'] = json_encode([implode('/', get_parent_ids($data['cat_id'], 'article_cat'))]);

            $this->assign('data', $data);
        }

        /*获取文章状态*/
        $BasicInfo = new BasicInfoModel();
        $stateList = $BasicInfo->getBasicList('article', 'AA');
        $this->assign('stateList' ,$stateList);

        //获取产品类目下拉列表，以children分好子数组
        $catListFormSelect = $this->currentModel->getCatTree();
        $this->assign('catListFormSelect' ,json_encode($catListFormSelect));
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
        //默认排序为1
        $param['sort_num'] = !empty($param['sort_num']) ? $param['sort_num'] : 1;
        try{
            //验证数据
            $result = $this->validate($param, 'Article');
            if ($result !== true) {
                throw new \Exception($result);
            }
            //处理缩略图
            $param['img_url'] = current(imgTempFileMove([$param['img_url']], 'admin_fussen/images/article/'));
            $this->currentModel->save($param);
        } catch (\Exception $e) {
            $msg = !empty($this->currentModel->getError()) ? $this->currentModel->getError() : $e->getMessage();
            $this->error($msg, null, ['token'=>$this->request->token()]);
        }

        $this->success('保存成功！', 'edit?art_id='.$this->currentModel->art_id);
    }


}

