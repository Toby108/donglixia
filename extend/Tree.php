<?php
/**
 * Created by PhpStorm.
 * User: seaso
 * Date: 2016/7/21
 * Time: 15:31
 */
class Tree
{
    public static $icon = array('├', '└','|----');

    /**
     * @param $data|\think\Collection|\think\Model  //待处理数组
     * @param string $id   //ID键
     * @param string $pid  //父ID键
     * @param string $child //记录子类键
     * @param int $root     //起始根，id为整型时 $root=0，id为字符串时 $root=''
     * @param bool $flag     //匹配不上根数组是否单独存放
     * @return array        //处理后数组(生成分好类的 多维数组)
     */
    public static function getTree($data, $id='id',$pid='pid',$child='child',$root=0,$flag=true) {
        $tree = array();
        if(is_array($data)){
            $array = array();
            foreach ($data as $key=>$item){
                if ($item instanceof think\Model || $item instanceof think\Collection) {
                    // 关联模型对象
                    if(count($data) == 1)
                    {
                        $data[$key] = $item->toArray();
                        return $data;
                    }
                    $data[$key] = $item->toArray();
                }
                else
                {
                    if(count($data) == 1)
                    {
                        return $data;
                    }
                }
                $array[$item[$id]] = &$data[$key];
            }
            foreach($data as $key=>$item){
                $parentId = $item[$pid];
                if($root == $parentId){
                    $tree[] =&$data[$key];
                }else{
                    if(isset($array[$parentId])){
                        $parent =&$array[$parentId];
                        $parent[$child][]=&$data[$key];
                    }
                    else
                    {
                        if($flag)
                        {
                            $tree[] =&$data[$key];
                        }
                    }
                }
            }
        }
        return $tree;
    }
    /**
     * @param $data array|\think\Collection|\think\Model 待处理数组
     * @param $value mixed 默认选中项ID
     * @param string $name 显示键名
     * @param string $id ID键
     * @param string $pid 父ID键
     * @param string $child 记录子类键
     * @param int $root 起始根
     * @return string 返回HTML<option>（下拉列表中使用，单选）
     */
    public static function get_option_tree($data,$value,$name='name',$id='id',$pid='pid',$child='child',$root=0)
    {
        $treeData = self::getTree($data,$id,$pid,$child,$root);
        $html = self::_get_option_tree($treeData,$id,$name,$value,$child);
        return $html;
    }

    /**
     * @param $data array|\think\Collection|\think\Model 待处理数组
     * @param array $value 默认选中项ID
     * @param string $name 显示键名
     * @param string $id ID键
     * @param string $pid 父ID键
     * @param string $child 记录子类键
     * @param int $root 起始根
     * @return string 返回HTML<option>（下拉列表中使用，多选）
     */
    public static function get_multiple_option_tree($data,$value=array(),$name='name',$id='id',$pid='pid',$child='child',$root=0)
    {
        $treeData = self::getTree($data,$id,$pid,$child,$root);
        $html = self::_get_multiple_option_tree($treeData,$id,$name,$value,$child);
        return $html;
    }

    /**
     * @param $data array|\think\Collection|\think\Model 待处理数组
     * @param string $name 显示键名
     * @param string $id ID键
     * @param string $pid 父ID键
     * @param string $child 记录子类键
     * @param int $root 起始根
     * @return string 数组（表格中使用，带前置符号|----）
     */
    public static function get_Table_tree($data,$name='name',$id='id',$pid='pid',$child='child',$root=0)
    {
        $treeData = self::getTree($data,$id,$pid,$child,$root);
        $array = self::_get_Table_tree($treeData,$id,$name,$child);
        return $array;
    }

    /**
     * @param $data array|\think\Collection|\think\Model 待处理数组
     * @param string $name 显示键名
     * @param string $id ID键
     * @param string $pid 父ID键
     * @param string $child 记录子类键
     * @param int $root 起始根
     * @return string 数组
     */
    public static function get_treeview($data,$name='name',$id='id',$pid='pid',$child='child',$root=0)
    {
        $treeData = self::getTree($data,$id,$pid,$child,$root);
        $array = self::_get_treeview($treeData,$id,$name,$child);
        return $array;
    }

    private static function _get_option_tree($data,$id,$name,$value,$child,$level=0,&$html="")
    {
        foreach ($data as $k => $item)
        {
            $tmp_str = str_repeat("&nbsp;&nbsp;",$level * 2);
            $tmp_str .= $level == 0 ? "" : (array_key_exists($child,$item) ?  self::$icon[1] : self::$icon[2]) ;
            $display = $tmp_str." ".$item[$name];
            if($value == $item[$id])
            {
                $html .= "<option selected value='$item[$id]'>$display</option>";
            }
            else
            {
                $html .= "<option value='$item[$id]'>$display</option>";
            }
            if(array_key_exists($child,$item))
            {
                self::_get_option_tree($item[$child],$id,$name,$value,$child,$level + 1,$html);
            }

        }
        return $html;
    }

    private static function _get_multiple_option_tree($data,$id,$name,$value=array(),$child,$level=0,&$html="")
    {
        foreach ($data as $k => $item)
        {
            $tmp_str = str_repeat("&nbsp;&nbsp;",$level * 2);
            $tmp_str .= $level == 0 ? "" : (array_key_exists($child,$item) ?  self::$icon[1] : self::$icon[2]) ;
            $display = $tmp_str." ".$item[$name];
            if(in_array($item[$id],$value))
            {
                $html .= "<option selected value='$item[$id]'>$display</option>";
            }
            else
            {
                $html .= "<option value='$item[$id]'>$display</option>";
            }
            if(array_key_exists($child,$item))
            {
                self::_get_multiple_option_tree($item[$child],$id,$name,$value,$child,$level + 1,$html);
            }

        }
        return $html;
    }


    private static function _get_Table_tree($data,$id,$name,$child,$level=0,&$array=[])
    {
        foreach ($data as $k => $item)
        {
            $tmp_str = str_repeat("&nbsp;&nbsp;",$level * 3);
//            $tmp_str .= $level == 0 ? "" : (array_key_exists($child,$item) || count($data) == ++$k ?  self::$icon[1] : self::$icon[2]) ;
            $tmp_str .= $level == 0 ? "" : (array_key_exists($child,$item) ?  self::$icon[1] : self::$icon[2]) ;
            $item[$name] = $tmp_str." ".$item[$name];
            $array[] = $item;
            if(array_key_exists($child,$item))
            {
                self::_get_Table_tree($item[$child],$id,$name,$child,$level + 1,$array);
            }

        }
        return $array;
    }

    private static function _get_treeview($data,$id,$name,$child,&$array=[])
    {
       return $array;
    }
}