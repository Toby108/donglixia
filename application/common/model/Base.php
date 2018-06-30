<?php
// +----------------------------------------------------------------------
// | Copyright (c) 2018-2018 http://www.donglixia.net All rights reserved.
// +----------------------------------------------------------------------
// | Author: 十万马 <962863675@qq.com>
// +----------------------------------------------------------------------
// | DateTime: 2018-02-09 16:17
// +----------------------------------------------------------------------

namespace app\common\model;

use think\Model;
use think\Db;
use think\Session;
use think\Request;

abstract class Base extends Model
{
    protected $autoWriteTimestamp = true;
    protected $apiMode = false;
    protected $autoSave = true;
    //save方法原始数据
    protected $saveData = [];
    protected $deleteTime = null;

    public function __construct($data = [])
    {
        parent::__construct($data);
        $db = $this->db(false);
        if (in_array('create_time',$db->getTableInfo('', 'fields'))) {
            $this->createTime = 'create_time';
        } else {
            $this->createTime = false;
        }
        if (in_array('modify_time',$db->getTableInfo('', 'fields'))) {
            $this->updateTime = 'modify_time';
        } else {
            $this->updateTime = false;
        }
        if (in_array('create_by',$db->getTableInfo('', 'fields')) && in_array('modify_by', $db->getTableInfo('', 'fields'))) {
            array_push($this->insert,'create_by','modify_by');
        }
        if (in_array('modify_by',$db->getTableInfo('', 'fields'))) {
            array_push($this->update,'modify_by');
        }
        if (in_array('language',$db->getTableInfo('', 'fields'))) {
            array_push($this->insert,'language');
        }
    }

    public function setCreateByAttr($value)
    {
        return $value ? $value : (!empty(Session::get('userInfo.user_id')) ? Session::get('userInfo.user_id') : 0);
    }

    public function setModifyByAttr($value)
    {
        return $value ? $value : (!empty(Session::get('userInfo.user_id')) ? Session::get('userInfo.user_id') : 0);
    }

    public function setLanguageAttr($value)
    {
        return $value ? $value : (!empty(Session::get('config.language')) ? Session::get('config.language') : 1);
    }

    public function getCreateTimeAttr($value)
    {
        return $value ? date('Y-m-d H:i:s', $value) : '';
    }

    public function getModifyTimeAttr($value)
    {
        return $value ? date('Y-m-d H:i:s', $value) : '';
    }

    public function getCreateByAttr($value)
    {
        return Db::name('user')->where('user_id', $value)->value('nick_name');
    }

    public function getModifyByAttr($value)
    {
        return Db::name('user')->where('user_id', $value)->value('nick_name');
    }

    /**
     * 是否API模式(API模式只会返回原始数据)
     * @param bool $apiMode
     * @return $this
     */
    public function setApiMode($apiMode = false)
    {
        $this->apiMode = $apiMode;
        return $this;
    }

    /**
     * 是否自动判断数据库新增或修改
     * @param bool $status
     * @return $this
     */
    public function autoSave($status = true)
    {
        $this->autoSave = $status;
        return $this;
    }


    /**
     * 保存当前数据对象
     * @access public
     * @param array  $data     数据
     * @param array  $where    更新条件
     * @param string $sequence 自增序列名
     * @return integer|false
     */
    public function save($data = [], $where = [], $sequence = null)
    {
        $data = !empty($data) ? $data : $this->getData();
        $this->saveData = $data;
        $this->allowField(true);
        if($this->autoSave)
        {
            $this->isUpdate = $this->hasPk($data);
        }

        $result =  parent::save($data,$where,$sequence);
        if ($result)
        {
            $this->dataChangelog($data);
        }
        return $result;
    }

    /**
     * 将新增和修改的数据写入日志
     * @param $data
     * @param $type int 类型：1insert，2update， 3删除
     */
    public function dataChangelog($data = [], $type = 0)
    {
        //过滤非表字段数据,反转字段名为键,获取与当前save数据的交集
        $paramsJson = !empty($this->field) ? array_intersect_key($data, array_flip($this->field)) : $data;
        $logData['table_name'] = $this->name;
        $logData['type'] = !empty($type) ? $type : ($this->hasPk($data) ? 2 : 1);
        $logData['content'] = json_encode($paramsJson, JSON_UNESCAPED_UNICODE);
        $logData['create_by'] = !empty(Session::get('userInfo.user_id')) ? Session::get('userInfo.user_id') :  (!empty($paramsJson['create_by']) ? $paramsJson['create_by'] : 0);
        $logData['create_time'] = time();
        Db::name('data_change_log')->insert($logData);
    }

    public function uploadImg($file,$path='')
    {
        $fileClass = Request::instance()->file($file);
        if($fileClass)
        {
            if($path)
            {
                $info = $fileClass->move(STATIC_PATH . '/img/' . $path, md5_file($fileClass->getInfo('tmp_name')));
                return VIEW_STATIC_PATH . '/img/' .$path.DS.$info->getSaveName();
            }
            else
            {
                $info = $fileClass->move(STATIC_PATH . '/img');
                return VIEW_STATIC_PATH . '/img'.$info->getSaveName();
            }
        }

        return false;
    }


    /**
     * 判断是否带有主键数据
     * @param array $data
     * @return bool
     */
    private function hasPk($data)
    {
        $find = $this->get($this->checkPkData($data));
        return empty($find) ? false : true;
    }

    /**
     * 得到主键数据
     * @param $data
     * @return array|int
     */
    private function checkPkData($data)
    {
        $pk = $this->getPk();
        if(is_array($pk) && count(array_intersect_key($data, array_flip($pk))) === count($pk))
        {
            return array_intersect_key($data, array_flip($pk));
        }
        elseif (is_string($pk))
        {
            return isset($data[$pk]) ? $data[$pk] : 0;
        }
        else
        {
            \think\Log::error($this->getTable().'未找到主键数据:'.json_encode($data));
            throw new \think\exception\HttpException(500, '请求数据异常');
        }
    }

    /**
     * 获取当前实例化后的模型对应的表名
     * @return bool|string
     */
    public function getTableName()
    {
        return $this->name;
    }
}