<?php
namespace kuaidi;

class Kdniao
{
    /**
     * 快递鸟申请地址：http://www.kdniao.com/ServiceApply.aspx
     * 生产环境地址：http://api.kdniao.cc/Ebusiness/EbusinessOrderHandle.aspx
     */
    protected $EBusinessID = '1265515';//快递鸟申请的商户ID
    protected $AppKey = '263fd6b9-58a3-4317-bd9c-abab8fc108b1';//电商加密私钥，快递鸟提供
    protected $ReqURL = 'http://api.kdniao.cc/Ebusiness/EbusinessOrderHandle.aspx';//请求物流信息url
    public function getData($number)
    {
        /*获取物流公司编码*/
        $shipper_info = json_decode($this->getComByJson($number),true);
        /*获取物流轨迹*/
        $logisticResult = $this->getOrderTracesByJson($shipper_info['Shippers'][0]['ShipperCode'], $number);
        $logisticResult = json_decode($logisticResult,true);
        $logisticResult += $shipper_info['Shippers'][0];
        $logisticResult['Traces'] = array_reverse($logisticResult['Traces']);//倒序排序

        return $logisticResult;
    }

    /**
     *  Json方式 查询物流公司名称
     * @param string $logisticCode 物流单号
     * @return mixed url响应返回的html
     */
    protected function getComByJson($logisticCode){
        $requestData= "{\"LogisticCode\":\"".$logisticCode."\"}";
        $datas = array(
            'EBusinessID' => $this->EBusinessID,
            'RequestType' => '2002',
            'RequestData' => urlencode($requestData) ,
            'DataType' => '2',
        );
        $datas['DataSign'] = $this->encrypt($requestData, $this->AppKey);
        $result=$this->sendPost($this->ReqURL, $datas);
        //根据公司业务处理返回的信息......
        return $result;
    }

    /**
     * Json方式 查询订单物流轨迹
     * @param string $shipperCode  物流公司编码
     * @param string $logisticCode 物流单号
     * @return mixed url 响应返回的html
     */
    protected function getOrderTracesByJson($shipperCode, $logisticCode){
        $requestData= "{\"OrderCode\":\"\",\"ShipperCode\":\"".$shipperCode."\",\"LogisticCode\":\"".$logisticCode."\",\"SiteUrl\":\"".$_SERVER['HTTP_HOST']."\",\"App\":\"php52demo\"}";
        $datas = array(
            'EBusinessID' => $this->EBusinessID,
            'RequestType' => '1002',
            'RequestData' => urlencode($requestData) ,
            'DataType' => '2',
        );

        $datas['DataSign'] = $this->encrypt($requestData, $this->AppKey);
        $result=$this->sendPost($this->ReqURL, $datas);

        //根据公司业务处理返回的信息......
        return $result;
    }

    /**
     * post提交数据
     * @param string $url 请求Url
     * @param array $datas 提交的数据
     * @return string url响应返回的html
     */
    protected function sendPost($url, $datas) {
        $temps = array();
        foreach ($datas as $key => $value) {
            $temps[] = sprintf('%s=%s', $key, $value);
        }
        $post_data = implode('&', $temps);
        $url_info = parse_url($url);
        $httpheader = "POST " . $url_info['path'] . " HTTP/1.0\r\n";
        $httpheader.= "Host:" . $url_info['host'] . "\r\n";
        $httpheader.= "Content-Type:application/x-www-form-urlencoded\r\n";
        $httpheader.= "Content-Length:" . strlen($post_data) . "\r\n";
        $httpheader.= "Connection:close\r\n\r\n";
        $httpheader.= $post_data;
        $fd = fsockopen($url_info['host'], 80);
        fwrite($fd, $httpheader);
        $gets = "";
        $headerFlag = true;
        while (!feof($fd)) {
            if (($header = @fgets($fd)) && ($header == "\r\n" || $header == "\n")) {
                break;
            }
        }
        while (!feof($fd)) {
            $gets.= fread($fd, 128);
        }
        fclose($fd);

        return $gets;
    }

    /**
     * 电商Sign签名生成
     * @param array $data 内容
     * @param string $appkey Appkey
     * @return string DataSign签名
     */
    protected function encrypt($data, $appkey) {
        return urlencode(base64_encode(md5($data.$appkey)));
    }
}














