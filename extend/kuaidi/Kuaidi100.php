<?php
namespace kuaidi;

class KuaiDi100
{
    /**
     * 获取物流信息
     * @param $number
     * @return mixed
     */
    public function getData($number)
    {
        $kuaidi100key = "b0c887c349f3d126";
        $arr = file_get_contents('http://www.kuaidi100.com/autonumber/auto?num='.$number);//查询快递公司代码code
        if(empty($arr)){
            return ['status'=>false,'msg'=>'查询失败！'];
        }
        /*通过公司代码code，转换为快递公司中文名称*/
        $res = json_decode($arr,true);
        $name = $this->getCompanyName($res[0]['comCode']);
        if(isset($res) && !empty($res[0]['comCode'])){
            $url = 'http://www.kuaidi100.com/applyurl?key='.$kuaidi100key.'&com='.$res[0]['comCode'].'&nu='.$number.'&show=2&muti=1&order=desc';
            $get_content = $this->http_curl($url);

            $data['status']=1;
            $data['number'] = $number;
            $data['company_name'] = $name;
            $data['message'] = $get_content;
        }else{
            $data['status']=0;
            $data['number'] = $number;
            $data['company_name'] = $name;
            $data['message'] ='抱歉，暂无记录';
        }
        return $data;
    }

    /**
     * 通过公司代码code，获取快递公司中文名称
     * @param $code
     * @return string
     */
    public function getCompanyName($code)
    {
        switch ($code){
            case "EMS":
                $res = 'ems';
                break;
            case "ems":
                $res = '中国邮政';
                break;
            case "shentong":
                $res = '申通快递';
                break;
            case "yuantong":
                $res = '圆通快递';
                break;
            case "shunfeng":
                $res = '顺丰速运';
                break;
            case "tiantian":
                $res = '天天快递';
                break;
            case "yunda":
                $res = '韵达快递';
                break;
            case "zhongtong":
                $res = '中通快递';
                break;
            case "longbanwuliu":
                $res = '龙邦物流';
                break;
            case "zhaijisong":
                $res = '宅急送';
                break;
            case "quanyikuaidi":
                $res = '全一快递';
                break;
            case "huitongkuaidi":
                $res = '汇通速递';
                break;
            case "minghangkuaidi":
                $res = '民航快递';
                break;
            case "yafengsudi":
                $res = '亚风速递';
                break;
            case "kuaijiesudi":
                $res = '快捷快递';
                break;
            case "tiandihuayu":
                $res = '华宇物流';
                break;
            case "zhongtiewuliu":
                $res = '中铁快运';
                break;
            case "haishihuitong":
                $res = '百世汇通';
                break;
            case "youshuwuliu":
                $res = '优速物流';
                break;
            case "quanfengkuaidi":
                $res = '全峰快递';
                break;
            case "debangwuliu":
                $res = '德邦';
                break;
            case "fedex":
                $res = 'FedEx';
                break;
            case "ups":
                $res = 'UPS';
                break;
            case "dhl":
                $res = 'DHL';
                break;
            case "shunfengen":
                $res = '顺丰';
                break;
            case "hengluwuliu":
                $res = '恒路物流';
                break;
            default:
                $res = '未知名';
        }
        return $res;
    }

    public function http_curl($url,  $data = '') {
        $ch = curl_init(); //初始化，创建一个curl 资源
        curl_setopt($ch, CURLOPT_URL, $url); //设置url
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); // 跳过证书检查
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE); // 从证书中检查SSL加密算法是否存在
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER,true);
        curl_setopt($ch, CURLOPT_POSTFIELDS,json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $ret = curl_exec($ch);//抓取url 并把它传递给浏览器
        curl_close($ch);//关闭curl资源，并且释放系统资源
        return $ret;
    }
}



